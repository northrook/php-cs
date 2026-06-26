<?php

declare(strict_types=1);

namespace Northrook\Dev\PHPStan\RequiresMemberRule;

use PHPStan\PhpDoc\Tag\MethodTag;
use PHPStan\ShouldNotHappenException;
use ReflectionException;
use ReflectionMethod;

final class ClassMethod extends RequiredMember
{
    protected const array MODIFIERS = [
        'final'    => true,
        'static'   => true,
        'abstract' => true,
    ];

    private null|ReflectionMethod $reflection = null;

    /**
     * @throws ShouldNotHappenException
     */
    public static function fromMethodTag(
        string $name,
        MethodTag $tag,
        string $requiredByType,
        string $requiredByClass,
    ): self {
        $member             = new self();
        $member->name       = $name;
        $member->modifiers  = $tag->isStatic() ? ['static' => 'static'] : [];
        $member->typeOf     = $member->explodeTypes($tag->getReturnType()->describe(\PHPStan\Type\VerbosityLevel::typeOnly()));
        $member->requiredBy = RequiredBy::from($requiredByType, $requiredByClass);

        return $member;
    }

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
}
