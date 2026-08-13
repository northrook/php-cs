<?php

declare(strict_types=1);

namespace Northrook\PHPStan\Internal;

use PHPStan\Reflection\ClassReflection;

/**
 * @internal
 */
final class PhpDocTag
{
    private function __construct() {}

    /**
     * @param string  $tag  e.g. `@static`
     */
    public static function has(
        string $phpDocComment,
        string $tag,
    ): bool {
        if (\str_contains($phpDocComment, "\r")) {
            $phpDocComment = \strtr($phpDocComment, ["\r\n" => "\n", "\r" => "\n"]);
        }

        foreach (\explode("\n", $phpDocComment) as $line) {
            if (\trim($line, " \n\r\t\v\0*/") === $tag) {
                return true;
            }
        }

        return false;
    }

    public static function classHas(
        ClassReflection $reflection,
        string          $tag,
    ): bool {
        $phpDocComment = $reflection->getNativeReflection()->getDocComment();

        return $phpDocComment !== false && self::has($phpDocComment, $tag);
    }

    /**
     * Label of the type that imposes `$tag` on `$reflection` via self, parents, or traits.
     *
     * @return null|non-empty-string
     */
    public static function imposedBy(
        ClassReflection $reflection,
        string          $tag,
    ): null|string {
        if (self::classHas($reflection, $tag)) {
            return ClassLabel::of($reflection);
        }

        foreach ($reflection->getParents() as $parent) {
            if (self::classHas($parent, $tag)) {
                return ClassLabel::of($parent);
            }
        }

        foreach ([$reflection, ...$reflection->getParents()] as $type) {
            foreach ($type->getTraits(true) as $trait) {
                if (self::classHas($trait, $tag)) {
                    return ClassLabel::of($trait);
                }
            }
        }

        return null;
    }
}
