<?php

declare(strict_types = 1);

namespace Northrook\Dev\PHPStan\RequiresMemberRule;

use PHPStan\Analyser\Scope;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\Reflection\ClassReflection;
use PHPStan\ShouldNotHappenException;
use Reflection;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperty;
use ReflectionType;
use Stringable;

/**
 * @internal
 * @factory
 */
abstract class RequiredMember implements Stringable
{
    private const array REMOVE_WHITESPACE = [
        ' '    => '',
        "\t"   => '',
        "\n"   => '',
        "\r"   => '',
        "\0"   => '',
        "\x0B" => '',
        '*'    => '',
    ];

    private const array TAG_MAP = [
        '@requires-const'    => self::CONST,
        '@requires-property' => self::PROPERTY,
        '@requires-method'   => self::METHOD,
    ];

    /** @var array<class-string<RequiredMember>, array{0:string, 1:string}> */
    private const array MEMBER_MAP = [
        RequiredMember::CONST    => ['Constant', 'const'],
        RequiredMember::PROPERTY => ['Property', 'property'],
        RequiredMember::METHOD   => ['Method', 'method'],
    ];

    public const string CONST = ClassConstant::class;

    public const string PROPERTY = ClassProperty::class;

    public const string METHOD = ClassMethod::class;

    /**
     * @var array<string,true>
     * @abstract
     */
    protected const array MODIFIERS = [];

    /** @var ReflectionClass */
    protected ReflectionClass $classReflection;

    protected string $className;

    protected Scope $scope;

    public RequiredBy $requiredBy;

    public readonly string $label;

    public readonly string $type;

    public string $name;

    /** @var array<non-empty-string,string> `[modifier => modifier]` */
    public array $modifiers;

    /** @var array<non-empty-string,string> `[type => type]` */
    public array $typeOf;

    private function __construct()
    {
        [$this->label, $this->type] = RequiredMember::MEMBER_MAP[$this::class];
    }

    /**
     * @param ClassReflection  $classReflection
     * @param Scope            $scope
     *
     * @return $this
     * @throws ShouldNotHappenException
     */
    final public function reflect(ClassReflection $classReflection, Scope $scope): self
    {
        if (! $classReflection->getNativeReflection() instanceof ReflectionClass) {
            throw new ShouldNotHappenException();
        }

        $this->classReflection ??= $classReflection->getNativeReflection();
        $this->className       ??= $classReflection->getName();
        $this->scope           ??= $scope;

        return $this;
    }

    /**
     * @return string[]
     * @throws ShouldNotHappenException
     */
    abstract protected function reflectionTypeOf(): array;

    abstract protected function getReflection(): ReflectionClassConstant|ReflectionProperty|ReflectionMethod;

    /**
     * @param null|string[]  $modifiers
     *
     * @return array|string[]
     */
    protected function resolveModifiers(null|array $modifiers = null): array
    {
        $modifiers ??= $this->modifiers;

        if (\array_key_exists('readonly', $modifiers) && $this->classReflection->isReadOnly()) {
            unset($modifiers['readonly']);
        }
        if (\array_key_exists('final', $modifiers) && $this->classReflection->isFinal()) {
            unset($modifiers['final']);
        }

        return $modifiers;
    }

    /**
     * @return string[]
     */
    protected function reflectionModifiers(): array
    {
        return $this->resolveModifiers(Reflection::getModifierNames($this->getReflection()->getModifiers()));
    }

    /**
     * @return bool
     * @throws ShouldNotHappenException
     */
    abstract public function notDeclared(): bool;

    /**
     * @return null|MemberDefinition
     * @throws ShouldNotHappenException
     */
    public function missingModifiers(): null|MemberDefinition
    {
        return MemberDefinition::validate($this->resolveModifiers(), $this->reflectionModifiers());
    }

    /**
     * @return null|MemberDefinition
     * @throws ShouldNotHappenException
     */
    public function missingTypeDeclarations(): null|MemberDefinition
    {
        return MemberDefinition::validate($this->typeOf, $this->reflectionTypeOf());
    }

    /**
     * @param string  $phpTagString
     *
     * @return self
     */
    protected function setModifiers(string &$phpTagString): self
    {
        $this->modifiers = [];
        $returnPhpTag    = [];

        foreach (\explode(' ', $phpTagString) as $segment) {
            // Always skip empty
            if (empty($segment)) {
                continue;
            }
            if (\array_key_exists($segment, static::MODIFIERS)) {
                $this->modifiers[$segment] = $segment;
            } else {
                $returnPhpTag[] = \trim($segment);
            }
        }

        $phpTagString = \implode(' ', $returnPhpTag);

        return $this;
    }

    protected function setTypeOf(string &$phpTagString): self
    {
        $this->typeOf = [];

        if (! \str_contains($phpTagString, ' ')) {
            return $this;
        }

        [$resolveTypes, $phpTagString] = \explode(' ', $phpTagString, 2);

        foreach ($this->explodeTypes($resolveTypes) as $resolveType) {
            $this->typeOf[$resolveType] = $resolveType;
        }

        return $this;
    }

    /**
     * @param non-empty-string  $nameString
     *
     * @return self
     */
    protected function setMemberName(string $nameString): self
    {
        $this->name = $nameString;

        return $this;
    }

    /**
     * @param string  $phpTagString
     * @param string  $requiredByType
     * @param string  $requiredByClass
     *
     * @return self
     * @throws ShouldNotHappenException
     */
    final public static function from(string $phpTagString, string $requiredByType, string $requiredByClass): self
    {
        // Extract the `@requires-{tag}`
        [$requiresTag, $phpTagString] = \explode(' ', \trim($phpTagString, " \t\n\r\0\x0B*"), 2);

        $member = self::newFrom($requiresTag)->setModifiers($phpTagString)->setTypeOf($phpTagString);

        $member->requiredBy = RequiredBy::from($requiredByType, $requiredByClass);

        $memberNameString = \trim($phpTagString);

        if ($memberNameString === '') {
            throw new ShouldNotHappenException(
                message: 'The remaining $phpTagString `'
                . $phpTagString
                . "` is somehow empty.\n"
                . 'That means no valid member name was provided, or a `set` step went very wrong.',
            );
        }

        if (\str_contains($memberNameString, ' ')) {
            throw new ShouldNotHappenException(
                message: 'The remaining $phpTagString `'
                . $phpTagString
                . "` contains whitespace.\n"
                . 'A `set` step went very wrong.',
            );
        }

        $member->setMemberName($memberNameString);

        return $member;
    }

    final public function __toString(): string
    {
        return $this->name;
    }

    final public function key(): string
    {
        return $this->type . '~' . $this->name;
    }

    public function name(false|string $className = false): string
    {
        if ($className === false) {
            return $this->name;
        }

        return $this instanceof ClassConstant ? $className . '::' . $this->name : $className . '->' . $this->name;
    }

    /**
     * @param null|ReflectionType|string  $resolveFrom
     *
     * @return non-empty-string[]
     */
    final protected function explodeTypes(null|string|ReflectionType $resolveFrom): array
    {
        if ($resolveFrom === null) {
            return [];
        }

        if ($resolveFrom instanceof ReflectionType) {
            $resolveFrom = \str_replace(['?'], ['null|'], $resolveFrom->__toString());
        }

        // TODO: [low] Allow for double-pipe OR `||` to mean any one of
        return \array_filter(\explode('|', \strtr($resolveFrom, self::REMOVE_WHITESPACE)));
    }

    /**
     * @param string  $requiresTag
     *
     * @return self
     * @throws ShouldNotHappenException
     */
    private static function newFrom(string $requiresTag): self
    {
        if (! \array_key_exists($requiresTag, self::TAG_MAP)) {
            throw new ShouldNotHappenException(
                message: 'Unknown @requires-tag `'
                . $requiresTag
                . " provided.\nExpected one of `"
                . \implode('|', \array_keys(self::TAG_MAP))
                . '`.',
            );
        }

        /** @var class-string<self> $required */
        $required = self::TAG_MAP[$requiresTag];

        return new $required();
    }
}
