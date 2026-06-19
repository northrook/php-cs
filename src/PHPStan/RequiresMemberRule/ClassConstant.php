<?php

declare(strict_types = 1);

namespace Northrook\Dev\PHPStan\RequiresMemberRule;

use PHPStan\ShouldNotHappenException;
use ReflectionClassConstant;

final class ClassConstant extends RequiredMember
{
    protected const array MODIFIERS = [
        'final'     => true,
        'public'    => true,
        'protected' => true,
        'private'   => true,
    ];

    private null|ReflectionClassConstant $reflection = null;

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
