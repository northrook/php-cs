<?php

declare(strict_types=1);

namespace Northrook\PHPStan;

use Northrook\PHPStan\Initializer\TaggedMethodCollector;
use Northrook\PHPStan\Internal\{ErrorHandler, NodeResolver};
use PhpParser\Node;
use PhpParser\Node\Expr\{Closure, MethodCall, Variable};
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\{Rule, RuleError};
use PHPStan\ShouldNotHappenException;

/**
 * Methods tagged `@initializer` initialize properties like a constructor.
 * They must be invoked from `__construct` (or another tagged initializer) only.
 *
 * @implements Rule<Node>
 */
final class InitializerRule implements Rule
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
        if ($node instanceof MethodCall) {
            return $this->processMethodCall($node, $scope);
        }

        if ($node instanceof Class_) {
            return $this->processClass($node);
        }

        return [];
    }

    /**
     * @return array<array-key, RuleError>
     *
     * @throws ShouldNotHappenException
     */
    private function processMethodCall(
        MethodCall $node,
        Scope      $scope,
    ): array {
        if (! $node->var instanceof Variable || $node->var->name !== 'this' || ! $node->name instanceof Identifier) {
            return [];
        }

        $classReflection = $scope->getClassReflection();

        if ($classReflection === null) {
            return [];
        }

        $methodName = $node->name->toString();
        $tagged     = TaggedMethodCollector::collect($classReflection)[\strtolower($methodName)] ?? null;

        if ($tagged === null || $this->isConstructorContext($scope, $classReflection)) {
            return [];
        }

        $className = $classReflection->getName();

        $this
            ->error(
                message   : "Method {$className}::{$methodName}() is @initializer and must only be called from __construct or another @initializer method.",
                identifier: 'initializer.calledOutsideConstructor',
            )
            ->line($node->getStartLine())
            ->tip("Tagged by {$tagged->sourceLabel()}.");

        return $this->errors();
    }

    /**
     * @return array<array-key, RuleError>
     *
     * @throws ShouldNotHappenException
     */
    private function processClass(
        Class_ $node,
    ): array {
        if ($node->isAnonymous()) {
            return [];
        }

        $className  = $this->resolveName($node);
        $reflection = $this->reflectionProvider->getClass($className);

        foreach (TaggedMethodCollector::collect($reflection) as $method) {
            if ($method->declaringClass->getName() !== $className) {
                continue;
            }

            if ($method->isConstructor) {
                $this->error(
                    message   : "Method {$className}::__construct() must not be tagged @initializer.",
                    identifier: 'initializer.redundant',
                );
            }

            if ($method->isStatic) {
                $this->error(
                    message   : "Static method {$className}::{$method->name}() must not be tagged @initializer.",
                    identifier: 'initializer.staticMethod',
                )->tip("Tagged by {$method->sourceLabel()}.");
            }
        }

        $introduced = TaggedMethodCollector::introduced($reflection);

        if ($introduced === []) {
            return $this->errors();
        }

        $calledFromConstructor = $this->directThisCallsInConstructor($node);
        $hasConstructor        = $this->hasConstructor($node);

        foreach ($introduced as $method) {
            if ($method->isStatic || $method->isConstructor) {
                continue;
            }

            if (\array_key_exists(\strtolower($method->name), $calledFromConstructor)) {
                continue;
            }

            $this->error(
                message   : "Method {$className}::__construct() must call @initializer method {$method->name}().",
                identifier: 'initializer.notCalledFromConstructor',
            )->tip("Introduced by {$method->sourceLabel()}.");

            if (! $hasConstructor) {
                break;
            }
        }

        return $this->errors();
    }

    private function isConstructorContext(
        Scope                               $scope,
        \PHPStan\Reflection\ClassReflection $classReflection,
    ): bool {
        $functionName = $scope->getFunctionName();

        if ($functionName === null) {
            return false;
        }

        if ($functionName === '__construct') {
            return true;
        }

        return isset(TaggedMethodCollector::collect($classReflection)[\strtolower($functionName)]);
    }

    /**
     * @return array<string, true> lowercased method names
     */
    private function directThisCallsInConstructor(
        Class_ $node,
    ): array {
        foreach ($node->getMethods() as $method) {
            if ($method->name->toString() !== '__construct' || $method->stmts === null) {
                continue;
            }

            $calls = [];
            $this->collectDirectThisCallsFromStmts($method->stmts, $calls);

            return $calls;
        }

        return [];
    }

    private function hasConstructor(
        Class_ $node,
    ): bool {
        foreach ($node->getMethods() as $method) {
            if ($method->name->toString() === '__construct') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<\PhpParser\Node\Stmt>  $stmts
     * @param array<string, true>  $calls
     */
    private function collectDirectThisCallsFromStmts(
        array  $stmts,
        array &$calls,
    ): void {
        foreach ($stmts as $stmt) {
            $this->collectDirectThisCallsFromNode($stmt, $calls);
        }
    }

    /**
     * @param array<string, true>  $calls
     */
    private function collectDirectThisCallsFromNode(
        Node   $node,
        array &$calls,
    ): void {
        if ($node instanceof MethodCall && $node->var instanceof Variable && $node->var->name === 'this' && $node->name instanceof Identifier) {
            $calls[\strtolower($node->name->toString())] = true;
        }

        if ($node instanceof Closure || $node instanceof Node\Expr\ArrowFunction) {
            return;
        }

        foreach ($node->getSubNodeNames() as $name) {
            $subNode = $node->$name;

            if ($subNode instanceof Node) {
                $this->collectDirectThisCallsFromNode($subNode, $calls);
            } elseif (\is_array($subNode)) {
                foreach ($subNode as $child) {
                    if ($child instanceof Node) {
                        $this->collectDirectThisCallsFromNode($child, $calls);
                    }
                }
            }
        }
    }

    public function getNodeType(): string
    {
        return Node::class;
    }
}
