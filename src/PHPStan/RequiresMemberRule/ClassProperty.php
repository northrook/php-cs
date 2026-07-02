<?php

declare(strict_types=1);

namespace Northrook\PHPStan\RequiresMemberRule;

use PHPStan\PhpDoc\Tag\PropertyTag;
use PHPStan\ShouldNotHappenException;
use ReflectionException;
use ReflectionProperty;

final class ClassProperty extends RequiredMember
{
    protected const array MODIFIERS = [
        'static'   => true,
        'readonly' => true,
    ];

    private null|ReflectionProperty $reflection = null;

    /**
     * @throws ShouldNotHappenException
     */
    public static function fromPropertyTag(
        string $name,
        PropertyTag $tag,
        string $requiredByType,
        string $requiredByClass,
    ): self {
        $member             = new self();
        $member->name       = $name;
        $member->modifiers  = [];
        $type = $tag->getReadableType() ?? $tag->getWritableType();
        $member->typeOf     = $member->explodeTypes(
            $type?->describe(\PHPStan\Type\VerbosityLevel::typeOnly()),
        );
        $member->requiredBy = RequiredBy::from($requiredByType, $requiredByClass);

        return $member;
    }

    public function notDeclared(): bool
    {
        return ! $this->classReflection->hasProperty($this->name);
    }

    protected function reflectionTypeOf(): array
    {
        return $this->explodeTypes($this->getReflection()->getType());
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
