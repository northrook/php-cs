<?php

declare(strict_types=1);

namespace Northrook\Dev\PHPStan\RequiresMemberRule;

use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\ShouldNotHappenException;

/**
 * @internal
 */
final class RequiredMemberCollector
{
    /**
     * @return array<string, RequiredMember>
     * @throws ShouldNotHappenException
     */
    public static function collect(ClassReflection $source): array
    {
        $resolved = $source->getResolvedPhpDoc();

        if ($resolved === null) {
            return [];
        }

        $requiredByType  = $source->getClassTypeDescription();
        $requiredByClass = $source->getName();
        $requiredMembers = [];

        foreach ($resolved->getMethodTags() as $name => $tag) {
            $member = ClassMethod::fromMethodTag($name, $tag, $requiredByType, $requiredByClass);

            $requiredMembers[$member->key()] = $member;
        }

        foreach ($resolved->getPropertyTags() as $name => $tag) {
            $member = ClassProperty::fromPropertyTag($name, $tag, $requiredByType, $requiredByClass);

            $requiredMembers[$member->key()] = $member;
        }

        foreach ($resolved->getPhpDocNodes() as $phpDocNode) {
            foreach ($phpDocNode->getTagsByName('@const') as $tagNode) {
                if (! $tagNode instanceof PhpDocTagNode) {
                    continue;
                }

                $value = $tagNode->value;

                if (! $value instanceof GenericTagValueNode) {
                    continue;
                }

                $member = ClassConstant::fromConstValue($value->value, $requiredByType, $requiredByClass);

                $requiredMembers[$member->key()] = $member;
            }
        }

        return $requiredMembers;
    }
}
