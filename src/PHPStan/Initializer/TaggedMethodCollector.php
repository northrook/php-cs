<?php

declare(strict_types=1);

namespace Northrook\PHPStan\Initializer;

use Northrook\PHPStan\Internal\PhpDocTag;
use PHPStan\Reflection\ClassReflection;

/**
 * @internal
 */
final class TaggedMethodCollector
{
    public const string TAG = '@initializer';

    private function __construct() {}

    /**
     * @return array<string, TaggedMethod>
     */
    public static function collect(
        ClassReflection $reflection,
    ): array {
        $methods = [];

        foreach (self::compositionSources($reflection) as $source) {
            foreach (self::collectFromSource($source) as $method) {
                $methods[\strtolower($method->name)] ??= $method;
            }
        }

        return $methods;
    }

    /**
     * Tagged methods newly introduced by `$class` — declared on the class itself,
     * or provided by a trait the class uses that no parent already exposes.
     *
     * @return array<string, TaggedMethod>
     */
    public static function introduced(
        ClassReflection $class,
    ): array {
        $all          = self::collect($class);
        $parentTagged = [];

        foreach ($class->getParents() as $parent) {
            foreach (self::collect($parent) as $method) {
                $parentTagged[\strtolower($method->name)] = true;
            }
        }

        $introduced = [];

        foreach ($all as $key => $method) {
            if ($method->declaringClass->getName() === $class->getName()) {
                $introduced[$key] = $method;

                continue;
            }

            if (! isset($parentTagged[$key])) {
                $introduced[$key] = $method;
            }
        }

        return $introduced;
    }

    /**
     * @return list<TaggedMethod>
     */
    private static function collectFromSource(
        ClassReflection $source,
    ): array {
        if ($source->isTrait()) {
            return self::collectTaggedNativeMethods($source);
        }

        $traitMethodNames = self::traitMethodNames($source);
        $methods          = [];

        foreach ($source->getNativeReflection()->getMethods() as $method) {
            if ($method->getDeclaringClass()->getName() !== $source->getName()) {
                continue;
            }

            if (isset($traitMethodNames[\strtolower($method->getName())])) {
                continue;
            }

            $tagged = self::taggedMethodFromNative($method, $source);

            if ($tagged !== null) {
                $methods[] = $tagged;
            }
        }

        return $methods;
    }

    /**
     * @return list<TaggedMethod>
     */
    private static function collectTaggedNativeMethods(
        ClassReflection $source,
    ): array {
        $methods = [];

        foreach ($source->getNativeReflection()->getMethods() as $method) {
            if ($method->getDeclaringClass()->getName() !== $source->getName()) {
                continue;
            }

            $tagged = self::taggedMethodFromNative($method, $source);

            if ($tagged !== null) {
                $methods[] = $tagged;
            }
        }

        return $methods;
    }

    private static function taggedMethodFromNative(
        \ReflectionMethod $method,
        ClassReflection   $source,
    ): null|TaggedMethod {
        $docComment = $method->getDocComment();

        if ($docComment === false || ! PhpDocTag::has($docComment, self::TAG)) {
            return null;
        }

        $name = $method->getName();

        return new TaggedMethod(
            name          : $name,
            declaringClass: $source,
            isStatic      : $method->isStatic(),
            isConstructor : $name === '__construct',
        );
    }

    /**
     * @return array<string, true>
     */
    private static function traitMethodNames(
        ClassReflection $class,
    ): array {
        $names = [];

        foreach ($class->getTraits(true) as $trait) {
            foreach ($trait->getNativeReflection()->getMethods() as $method) {
                if ($method->getDeclaringClass()->getName() !== $trait->getName()) {
                    continue;
                }

                $names[\strtolower($method->getName())] = true;
            }
        }

        return $names;
    }

    /**
     * @return list<ClassReflection>
     */
    private static function compositionSources(
        ClassReflection $reflection,
    ): array {
        /** @var list<ClassReflection> $sources */
        $sources = [$reflection];

        foreach ($reflection->getParents() as $parent) {
            $sources[] = $parent;
        }

        foreach ([$reflection, ...$reflection->getParents()] as $type) {
            foreach ($type->getTraits(true) as $trait) {
                $sources[] = $trait;
            }
        }

        return $sources;
    }
}
