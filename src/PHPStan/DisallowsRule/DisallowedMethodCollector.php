<?php

declare(strict_types=1);

namespace Northrook\PHPStan\DisallowsRule;

use Northrook\PHPStan\Internal\ClassLabel;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Reflection\ClassReflection;

/**
 * @internal
 */
final class DisallowedMethodCollector
{
    private function __construct() {}

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

        $sourceLabel = ClassLabel::of($source);
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

                foreach (self::parse($value->value, $sourceLabel) as $method) {
                    $methods[$method->key()] = $method;
                }
            }
        }

        return $methods;
    }

    /**
     * @param non-empty-string  $sourceLabel
     *
     * @return list<DisallowedMethod>
     */
    private static function parse(
        string $value,
        string $sourceLabel,
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

            $methods[] = new DisallowedMethod(
                name       : $matches[2],
                isStatic   : $matches[1] === 'static',
                sourceLabel: $sourceLabel,
            );
        }

        return $methods;
    }
}
