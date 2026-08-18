<?php

declare(strict_types=1);

namespace Northrook\PHPStan;

use Northrook\PHPStan\Initializer\TaggedMethodCollector;
use PHPStan\Reflection\{AdditionalConstructorsExtension, ClassReflection};

final class TaggedAdditionalConstructorsExtension implements AdditionalConstructorsExtension
{
    /**
     * @return list<string>
     */
    public function getAdditionalConstructors(
        ClassReflection $classReflection,
    ): array {
        if ($classReflection->isInterface() || $classReflection->isEnum() || $classReflection->isTrait()) {
            return [];
        }

        $methods = [];

        foreach (TaggedMethodCollector::collect($classReflection) as $method) {
            if ($method->isStatic || $method->isConstructor) {
                continue;
            }

            $methods[] = $method->name;
        }

        return $methods;
    }
}
