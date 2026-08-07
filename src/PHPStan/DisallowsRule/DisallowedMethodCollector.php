<?php

declare(strict_types=1);

namespace Northrook\PHPStan\DisallowsRule;

use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Reflection\ClassReflection;

/**
 * @internal
 */
final class DisallowedMethodCollector
{
    /**
     * @return array<string, DisallowedMethod>
     */
    public static function collect(
        ClassReflection $source,
    ): array {
        $resolved = $source->getResolvedPhpDoc();

        if ($resolved === null) {
            return [];
        }

        $sourceLabel = self::label($source);
        $sourceClass = $source->getName();
        $methods     = [];

        foreach ($resolved->getPhpDocNodes() as $phpDocNode) {
            foreach ($phpDocNode->getTagsByName('@disallows') as $tagNode) {
                if (! $tagNode instanceof PhpDocTagNode) {
                    continue;
                }

                $value = $tagNode->value;

                if (! $value instanceof GenericTagValueNode) {
                    continue;
                }

                foreach (self::parse($value->value, $sourceLabel, $sourceClass) as $method) {
                    $methods[$method->key()] = $method;
                }
            }
        }

        return $methods;
    }

    /**
     * @param non-empty-string  $sourceLabel
     * @param class-string      $sourceClass
     *
     * @return list<DisallowedMethod>
     */
    private static function parse(
        string $value,
        string $sourceLabel,
        string $sourceClass,
    ): array {
        $value = \trim($value);

        if ($value === '') {
            return [];
        }

        $methods = [];

        foreach (\explode(',', $value) as $segment) {
            $segment = \trim($segment);

            if ($segment === '') {
                continue;
            }

            if (
                \preg_match(
                    '/^(?:(static)\s+)?([a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)\s*(?:\(\s*\))?\s*$/',
                    $segment,
                    $matches,
                ) !== 1
            ) {
                continue;
            }

            $name = $matches[2];

            $methods[] = new DisallowedMethod(
                name       : $name,
                isStatic   : $matches[1] === 'static',
                sourceLabel: $sourceLabel,
                sourceClass: $sourceClass,
            );
        }

        return $methods;
    }

    /**
     * @return non-empty-string
     */
    private static function label(
        ClassReflection $reflection,
    ): string {
        if ($reflection->isTrait()) {
            return 'trait ' . $reflection->getName();
        }

        if ($reflection->isInterface()) {
            return 'interface ' . $reflection->getName();
        }

        if ($reflection->isEnum()) {
            return 'enum ' . $reflection->getName();
        }

        if ($reflection->isAbstract()) {
            return 'abstract class ' . $reflection->getName();
        }

        return 'class ' . $reflection->getName();
    }
}
