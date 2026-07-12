<?php

declare(strict_types = 1);

namespace Northrook\PHPStan;

use Northrook\PHPStan\Internal\{ErrorHandler, NodeResolver, PhpDocTag};
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\{Rule, RuleError};
use PHPStan\ShouldNotHappenException;

/**
 * Classes tagged `@singleton` must implement SingletonInterface.
 *
 * @implements Rule<Class_>
 */
final class SingletonClassRule implements Rule
{
    use ErrorHandler;
    use NodeResolver;

    private const string TAG = '@singleton';

    private const string INTERFACE = 'Northrook\\Contracts\\Interfaces\\SingletonInterface';

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
    ) {}

    /**
     * @return array<array-key, RuleError>
     *
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node instanceof Class_) {
            return [];
        }

        $className  = $this->resolveName($node);
        $reflection = $this->reflectionProvider->getClass($className);

        if (! PhpDocTag::classHas($reflection, self::TAG)) {
            return [];
        }

        if ($reflection->implementsInterface(self::INTERFACE)) {
            return [];
        }

        $this->error(
            message: "Class {$className} is @singleton but does not implement " . self::INTERFACE . '.',
            identifier: 'singleton.missingInterface',
        )->tip('Extend Northrook\\Contracts\\Singleton or implement the interface directly.');

        return $this->errors();
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }
}
