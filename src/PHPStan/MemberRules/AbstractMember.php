<?php

declare(strict_types=1);

namespace Northrook\PHPStan\MemberRules;

use Northrook\PHPStan\Internal\PhpDocTag;
use PHPStan\ShouldNotHappenException;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperty;

/**
 * @internal
 */
final readonly class AbstractMember
{
    private function __construct(
        public string $requiredBy,
        public string $definition,
        public string $name,
        public string $key,
    ) {}

    public function name(
        null|string $className = null,
    ): string {
        if ($className === null) {
            return $this->name;
        }

        return $this->definition === 'Constant' ? $className . '::' . $this->name : $className . '->' . $this->name;
    }

    /**
     * @throws ShouldNotHappenException
     */
    public static function from(
        ReflectionClassConstant|ReflectionProperty|ReflectionMethod $memberReflection,
        string                                                      $requiredBy,
    ): null|AbstractMember {
        $phpDocComment = $memberReflection->getDocComment();

        if ($phpDocComment === false || ! PhpDocTag::has($phpDocComment, '@abstract')) {
            return null;
        }

        $member = match (true) {
            $memberReflection instanceof ReflectionClassConstant => 'Constant',
            $memberReflection instanceof ReflectionProperty => 'Property',
            $memberReflection instanceof ReflectionMethod => 'Method',
        };

        $name = $memberReflection->getName();

        if (! $name) {
            throw new ShouldNotHappenException;
        }

        return new self($requiredBy, $member, $name, $member . '~' . $name);
    }
}
