<?php

declare(strict_types=1);

namespace Northrook\PHPStan\Internal;

use PHPStan\Reflection\ClassReflection;

/**
 * @internal
 */
final class PhpDocTag
{
    /**
     * @param string  $phpDocComment
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
        string $tag,
    ): bool {
        $phpDocComment = $reflection->getNativeReflection()->getDocComment();

        return $phpDocComment !== false && self::has($phpDocComment, $tag);
    }
}
