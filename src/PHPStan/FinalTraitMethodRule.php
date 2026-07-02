<?php

declare(strict_types = 1);

namespace Northrook\PHPStan;

use Northrook\PHPStan\Internal\{ErrorHandler, NodeResolver};
use PhpParser\Node;
use PhpParser\Node\Stmt\{ClassLike, Class_, Enum_, Trait_};
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\{Rule, RuleError};
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<ClassLike>
 */
final class FinalTraitMethodRule implements Rule
{
    use ErrorHandler;
    use NodeResolver;

    /**
     * @param list<string>  $testDirectories  path segments that mark a file as test-only, where breaking a trait's seal is allowed
     */
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly array $testDirectories = ['tests'],
    ) {}

    /**
     * @return array<array-key, RuleError>
     *
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node instanceof Class_ && ! $node instanceof Trait_ && ! $node instanceof Enum_) {
            return [];
        }

        if ($node->getTraitUses() === []) {
            return [];
        }

        if ($this->isTestFile($scope->getFile())) {
            return [];
        }

        $className  = $this->resolveName($node);
        $reflection = $this->reflectionProvider->getClass($className);

        $finalTraitMethods = $this->finalTraitMethods($reflection);

        if ($finalTraitMethods === []) {
            return [];
        }

        foreach ($node->getMethods() as $classMethod) {
            $sealedBy = $finalTraitMethods[\strtolower($classMethod->name->toString())] ?? null;

            if ($sealedBy === null) {
                continue;
            }

            $method = $className . '::' . $classMethod->name->toString() . '()';

            $this->error(
                message: "Method {$method} overrides final method sealed by trait {$sealedBy}.",
                identifier: 'finalTraitMethod.overridden',
            )
                ->line($classMethod->getStartLine())
                ->tip('PHP does not enforce a trait\'s `final` on the using class, silently breaking the seal.');
        }

        return $this->errors();
    }

    /**
     * @return array<string, string> lowercased method name => declaring trait
     */
    private function finalTraitMethods(\PHPStan\Reflection\ClassReflection $reflection): array
    {
        $finalTraitMethods = [];

        foreach ($reflection->getTraits() as $trait) {
            foreach ($trait->getNativeReflection()->getMethods() as $method) {
                if (! $method->isFinal()) {
                    continue;
                }

                $finalTraitMethods[\strtolower($method->getName())] = $method->getDeclaringClass()->getName();
            }
        }

        return $finalTraitMethods;
    }

    private function isTestFile(string $file): bool
    {
        if ($this->testDirectories === []) {
            return false;
        }

        $file = \str_replace('\\', '/', $file);

        foreach ($this->testDirectories as $directory) {
            $directory = \trim($directory, '/');

            if ($directory === '') {
                continue;
            }

            if (\preg_match('#(^|/)' . \preg_quote($directory, '#') . '(/|$)#', $file) === 1) {
                return true;
            }
        }

        return false;
    }

    public function getNodeType(): string
    {
        return ClassLike::class;
    }
}
