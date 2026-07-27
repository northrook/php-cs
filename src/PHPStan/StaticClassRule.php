<?php

declare(strict_types=1);

namespace Northrook\PHPStan;

use Northrook\PHPStan\Internal\{ErrorHandler, NodeResolver, PhpDocTag};
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\{ClassReflection, ReflectionProvider};
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
        Node $node,
        Scope $scope,
    ): array {
        if (! $node instanceof Class_ || $node->isAnonymous()) {
            return [];
        }

        $className  = $this->resolveName($node);
        $reflection = $this->reflectionProvider->getClass($className);

        $imposedBy = $this->staticImposedBy($reflection);

        if ($imposedBy === null) {
            return [];
        }

        $constructor = $reflection->getNativeReflection()->getConstructor();

        if ($constructor !== null && ! $constructor->isPublic()) {
            return [];
        }

        $visibility = $constructor === null ? 'no' : 'a public';

        $this->error(
            message: "Class {$className} is @static but has {$visibility} constructor.",
            identifier: 'staticClass.publicConstructor',
        )->tip("Imposed by {$imposedBy}.");

        return $this->errors();
    }

    /**
     * @return null|string  labelling type that imposed `@static`
     */
    private function staticImposedBy(
        ClassReflection $reflection,
    ): null|string {
        if (PhpDocTag::classHas($reflection, self::TAG)) {
            return $this->label($reflection);
        }

        foreach ($reflection->getParents() as $parent) {
            if (PhpDocTag::classHas($parent, self::TAG)) {
                return $this->label($parent);
            }
        }

        foreach ([$reflection, ...$reflection->getParents()] as $type) {
            $imposedBy = $this->staticImposedByTraits($type);

            if ($imposedBy !== null) {
                return $imposedBy;
            }
        }

        return null;
    }

    /**
     * @return null|string  labelling trait that imposed `@static`
     */
    private function staticImposedByTraits(
        ClassReflection $type,
    ): null|string {
        foreach ($type->getTraits() as $trait) {
            if (PhpDocTag::classHas($trait, self::TAG)) {
                return $this->label($trait);
            }

            $imposedBy = $this->staticImposedByTraits($trait);

            if ($imposedBy !== null) {
                return $imposedBy;
            }
        }

        return null;
    }

    private function label(
        ClassReflection $reflection,
    ): string {
        if ($reflection->isTrait()) {
            return 'trait ' . $reflection->getName();
        }

        if ($reflection->isAbstract()) {
            return 'abstract class ' . $reflection->getName();
        }

        return 'class ' . $reflection->getName();
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }
}
