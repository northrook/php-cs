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
 * Classes tagged `@static` (or constrained via parent/trait) must have a non-public constructor.
 *
 * @implements Rule<Class_>
 */
final class StaticClassRule implements Rule
{
    use ErrorHandler;
    use NodeResolver;

    private const string TAG = '@static';

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

        if ($imposedBy === null) {
            return [];
        }

        $constructor = $reflection->getNativeReflection()->getConstructor();

        if ($constructor !== null && ! $constructor->isPublic()) {
            return [];
        }

        $visibility = $constructor === null ? 'no' : 'a public';

        $this->error(
            message   : "Class {$className} is @static but has {$visibility} constructor.",
            identifier: 'staticClass.publicConstructor',
        )->tip("Imposed by {$imposedBy}.");

        return $this->errors();
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }
}
