<?php

declare(strict_types=1);

namespace Northrook\PHPStan\RequiresMemberRule;

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

    /** @var array<class-string<RequiredMember>, array{0:string, 1:string}> */
    private const array MEMBER_MAP = [
        RequiredMember::CONST    => ['Constant', 'const'],
        RequiredMember::PROPERTY => ['Property', 'property'],
        RequiredMember::METHOD   => ['Method', 'method'],
    ];

    public const string CONST = ClassConstant::class;

    public const string PROPERTY = ClassProperty::class;

    public const string METHOD = ClassMethod::class;

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

    protected function __construct()
    {
        [$this->label, $this->type] = RequiredMember::MEMBER_MAP[$this::class];
    }

    /**
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
     * @param null|string[] $modifiers
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
     * @throws ShouldNotHappenException
     */
    abstract public function notDeclared(): bool;

    /**
     * @throws ShouldNotHappenException
     */
    public function missingModifiers(): null|MemberDefinition
    {
        return MemberDefinition::validate($this->resolveModifiers(), $this->reflectionModifiers());
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function missingTypeDeclarations(): null|MemberDefinition
    {
        return MemberDefinition::validate($this->typeOf, $this->reflectionTypeOf());
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
}
