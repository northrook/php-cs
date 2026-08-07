<?php

declare(strict_types=1);

namespace Northrook\PHPStan;

use Northrook\PHPStan\DisallowsRule\{DisallowedMethod, DisallowedMethodCollector};
use Northrook\PHPStan\Internal\{ErrorHandler, NodeResolver};
use PhpParser\Node;
use PhpParser\Node\Stmt\{Class_, ClassLike, Enum_, Interface_, Trait_};
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\{ClassReflection, ReflectionProvider};
use PHPStan\Rules\{Rule, RuleError};
use PHPStan\ShouldNotHappenException;

/**
 * Types tagged `@disallows` (and every consumer) must not end up with those methods.
 *
 * @implements Rule<ClassLike>
 */
final class DisallowsMethodRule implements Rule
{
    use ErrorHandler;
    use NodeResolver;

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
    ) {}

    /**
     * @return array<array-key, RuleError>
     *
     * @throws ShouldNotHappenException
     */
    public function processNode(
        Node  $node,
        Scope $scope,
    ): array {
        if (
            ! $node instanceof Class_
            && ! $node instanceof Interface_
            && ! $node instanceof Trait_
            && ! $node instanceof Enum_
        ) {
            return [];
        }

        if ($node instanceof Class_ && $node->isAnonymous()) {
            return [];
        }

        $className  = $this->resolveName($node);
        $reflection = $this->reflectionProvider->getClass($className);
        $disallowed = $this->disallowedMethods($reflection);

        if ($disallowed === []) {
            return [];
        }

        foreach ($disallowed as $method) {
            if (! $this->hasDisallowedMethod($reflection, $method)) {
                continue;
            }

            $this->error(
                message   : "Method {$className}::{$method->display()} is disallowed.",
                identifier: 'disallows.methodPresent',
            )->tip("Disallowed by {$method->sourceLabel}.");
        }

        return $this->errors();
    }

    /**
     * @return array<string, DisallowedMethod>
     */
    private function disallowedMethods(
        ClassReflection $reflection,
    ): array {
        $disallowed = [];

        foreach ($this->compositionSources($reflection) as $source) {
            foreach (DisallowedMethodCollector::collect($source) as $method) {
                $disallowed[$method->key()] ??= $method;
            }
        }

        return $disallowed;
    }

    /**
     * @return list<ClassReflection>
     */
    private function compositionSources(
        ClassReflection $reflection,
    ): array {
        /** @var list<ClassReflection> $sources */
        $sources = [$reflection];

        foreach ($reflection->getInterfaces() as $interface) {
            $sources[] = $interface;
        }

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

    private function hasDisallowedMethod(
        ClassReflection  $reflection,
        DisallowedMethod $method,
    ): bool {
        if (! $reflection->hasNativeMethod($method->name)) {
            return false;
        }

        return $reflection->getNativeMethod($method->name)->isStatic() === $method->isStatic;
    }

    public function getNodeType(): string
    {
        return ClassLike::class;
    }
}
