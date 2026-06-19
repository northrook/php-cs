<?php

declare(strict_types = 1);

namespace Northrook\Dev\PHPStan\RequiresMemberRule;

use PHPStan\ShouldNotHappenException;
use ReflectionException;
use ReflectionMethod;

final class ClassMethod extends RequiredMember
{
    protected const array MODIFIERS = [
        'final'     => true,
        'static'    => true,
        'abstract'  => true,
        'public'    => true,
        'protected' => true,
        'private'   => true,
    ];

    private null|ReflectionMethod $reflection = null;

    public function notDeclared(): bool
    {
        return ! $this->classReflection->hasMethod($this->name);
    }

    protected function getReflection(): ReflectionMethod
    {
        try {
            return $this->reflection ??= $this->classReflection->getMethod($this->name);
        } catch (ReflectionException $exception) {
            throw new ShouldNotHappenException(message: $exception->getMessage());
        }
    }

    protected function reflectionTypeOf(): array
    {
        return $this->explodeTypes($this->getReflection()->getReturnType());
    }

    protected function setTypeOf(string &$phpTagString): RequiredMember
    {
        $this->typeOf = [];

        $typeOperator = \strrpos($phpTagString, ':');

        if ($typeOperator === false) {
            return $this;
        }

        $resolveTypes = \substr($phpTagString, $typeOperator + 1);
        $phpTagString = \substr($phpTagString, 0, $typeOperator);

        foreach ($this->explodeTypes($resolveTypes) as $resolveType) {
            $this->typeOf[$resolveType] = $resolveType;
        }

        return $this;
    }

    protected function setMemberName(string $nameString): self
    {
        $this->name = \trim($nameString, '()');

        return $this;
    }
}
