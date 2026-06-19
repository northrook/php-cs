<?php

declare(strict_types = 1);

namespace Northrook\Dev\PHPStan\MemberRules;

use PHPStan\Reflection\ClassReflection;
use PHPStan\ShouldNotHappenException;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperty;

final readonly class AbstractMember
{
    /**
     * @param string  $phpDocComment
     * @param string  $requiredBy
     * @param string  $declaredBy
     * @param string  $definition
     * @param string  $name
     * @param string  $key
     */
    private function __construct(
        public string $phpDocComment,
        public string $requiredBy,
        public string $declaredBy,
        public string $definition,
        public string $name,
        public string $key,
    ) {}

    public function name(false|string $className = false): string
    {
        if ($className === false) {
            return $this->name;
        }

        return $this->definition === 'Constant' ? $className . '::' . $this->name : $className . '->' . $this->name;
    }

    /**
     * @param ReflectionClassConstant|ReflectionMethod|ReflectionProperty  $memberReflection
     * @param string                                                       $requiredBy
     *
     * @return ?AbstractMember
     * @throws \PHPStan\ShouldNotHappenException
     */
    public static function from(
        ReflectionClassConstant|ReflectionProperty|ReflectionMethod $memberReflection,
        string $requiredBy,
    ): null|AbstractMember {
        $phpDocComment = $memberReflection->getDocComment();

        if (! $phpDocComment || ! \str_contains($phpDocComment, '@abstract')) {
            return null;
        }

        $declaredBy = $memberReflection->getDeclaringClass()->getName();

        $member = match (true) {
            $memberReflection instanceof ReflectionClassConstant => 'Constant',
            $memberReflection instanceof ReflectionProperty => 'Property',
            $memberReflection instanceof ReflectionMethod => 'Method',
        };

        $name = $memberReflection->getName();

        if (! $name) {
            throw new ShouldNotHappenException();
        }

        return new self($phpDocComment, $requiredBy, $declaredBy, $member, $name, $member . '~' . $name);
    }
}
