<?php

declare(strict_types=1);

namespace Northrook\PHPStan\Internal;

use PHPStan\Reflection\ClassReflection;

/**
 * @internal
 */
final class ClassLabel
{
    private function __construct() {}

    /**
     * @return non-empty-string
     */
    public static function of(
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
