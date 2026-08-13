<?php

declare(strict_types=1);

namespace Northrook\PHPStan;

use Northrook\PHPStan\Internal\{ErrorHandler, NodeResolver, PhpDocTag};
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\{Rule, RuleError};
use PHPStan\ShouldNotHappenException;

/**
 * Classes tagged `@singleton` (or constrained via parent/trait) must extend
 * {@see \Northrook\Contracts\Singleton}.
 *
 * @implements Rule<Class_>
 */
final class SingletonClassRule implements Rule
{
    use ErrorHandler;
    use NodeResolver;

    private const string TAG = '@singleton';

    private const string BASE = 'Northrook\\Contracts\\Singleton';

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
        if (! $node instanceof Class_ || $node->isAnonymous()) {
            return [];
        }

        $className  = $this->resolveName($node);
        $reflection = $this->reflectionProvider->getClass($className);
        $imposedBy  = PhpDocTag::imposedBy($reflection, self::TAG);

        if ($imposedBy === null || $reflection->is(self::BASE)) {
            return [];
        }

        $this->error(
            message   : "Class {$className} is @singleton but does not extend " . self::BASE . '.',
            identifier: 'singleton.missingBase',
        )->tip("Imposed by {$imposedBy}.");

        return $this->errors();
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }
}
