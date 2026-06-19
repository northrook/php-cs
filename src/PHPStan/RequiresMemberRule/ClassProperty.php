<?php

declare(strict_types = 1);

namespace Northrook\Dev\PHPStan\RequiresMemberRule;

use PHPStan\ShouldNotHappenException;
use ReflectionException;
use ReflectionProperty;

final class ClassProperty extends RequiredMember
{
    private null|ReflectionProperty $reflection = null;

    protected const array MODIFIERS = [
        'static'    => true,
        'readonly'  => true,
        'public'    => true,
        'protected' => true,
        'private'   => true,
    ];

    public function notDeclared(): bool
    {
        return ! $this->classReflection->hasProperty($this->name);
    }

    protected function reflectionTypeOf(): array
    {
        return $this->explodeTypes($this->getReflection()->getType());
    }

    protected function setMemberName(string $nameString): self
    {
        $this->name = \trim($nameString, '$');

        return $this;
    }

    protected function getReflection(): ReflectionProperty
    {
        try {
            return $this->reflection ??= $this->classReflection->getProperty($this->name);
        } catch (ReflectionException $exception) {
            throw new ShouldNotHappenException(message: $exception->getMessage());
        }
    }
}
