<?php

declare(strict_types=1);

namespace Northrook\Dev\PHPStan\RequiresMemberRule;

use PHPStan\ShouldNotHappenException;
use ReflectionClassConstant;

final class ClassConstant extends RequiredMember
{
    protected const array MODIFIERS = [
        'final' => true,
    ];

    private null|ReflectionClassConstant $reflection = null;

    /**
     * @throws ShouldNotHappenException
     */
    public static function fromConstValue(
        string $value,
        string $requiredByType,
        string $requiredByClass,
    ): self {
        $value = \trim($value);

        if ($value === '') {
            throw new ShouldNotHappenException(
                message: '@const tag value is empty.',
            );
        }

        $member             = new self();
        $member->modifiers  = [];
        $member->requiredBy = RequiredBy::from($requiredByType, $requiredByClass);

        $segments = \preg_split('/\s+/', $value) ?: [];

        if ($segments === []) {
            throw new ShouldNotHappenException(
                message: '@const tag value `'
                . $value
                . '` could not be parsed.',
            );
        }

        $name = \array_pop($segments);

        if ($name === null || $name === '') {
            throw new ShouldNotHappenException(
                message: '@const tag value `'
                . $value
                . '` has no constant name.',
            );
        }

        $member->name   = $name;
        $member->typeOf = $member->explodeTypes($segments === [] ? null : \implode(' ', $segments));

        return $member;
    }

    public function notDeclared(): bool
    {
        return ! $this->classReflection->hasConstant($this->name);
    }

    protected function reflectionTypeOf(): array
    {
        return $this->explodeTypes($this->getReflection()->getType());
    }

    protected function getReflection(): ReflectionClassConstant
    {
        return $this->reflection ??= $this->classReflection->getReflectionConstant($this->name)
        ?: throw new ShouldNotHappenException();
    }
}
