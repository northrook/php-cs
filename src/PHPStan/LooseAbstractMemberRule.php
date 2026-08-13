<?php

declare(strict_types=1);

namespace Northrook\PHPStan;

use Northrook\PHPStan\Internal\{ClassLabel, ErrorHandler, NodeResolver};
use Northrook\PHPStan\MemberRules\AbstractMember;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\{ClassReflection, ReflectionProvider};
use PHPStan\Rules\{Rule, RuleError};
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<Class_>
 */
final class LooseAbstractMemberRule implements Rule
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
     * @throws \PHPStan\Reflection\MissingConstantFromReflectionException
     * @throws \PHPStan\Reflection\MissingMethodFromReflectionException
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

        foreach ($this->requiredMembers($reflection, $className, $scope) as $member) {
            if ($member === false) {
                continue;
            }

            $this->error(
                message   : 'Missing ' . $member->definition . ' ' . $member->name($className) . '.',
                identifier: 'abstractMember.notFound',
            )->tip($member->requiredBy);
        }

        return $this->errors();
    }

    /**
     * @param class-string  $className
     *
     * @return array<string, false|AbstractMember>
     *
     * @throws ShouldNotHappenException
     * @throws \PHPStan\Reflection\MissingConstantFromReflectionException
     * @throws \PHPStan\Reflection\MissingMethodFromReflectionException
     */
    private function requiredMembers(
        ClassReflection $reflection,
        string          $className,
        Scope           $scope,
    ): array {
        $requiredMembers = [];
        $sources         = [...$reflection->getParents()];

        foreach ([$reflection, ...$reflection->getParents()] as $type) {
            foreach ($type->getTraits(true) as $trait) {
                $sources[] = $trait;
            }
        }

        foreach ($sources as $source) {
            $requiredBy       = ClassLabel::of($source);
            $nativeReflection = $source->getNativeReflection();

            foreach ($nativeReflection->getReflectionConstants() as $reflectionConstant) {
                $constant = AbstractMember::from($reflectionConstant, $requiredBy);

                if (! $constant) {
                    continue;
                }

                if ($reflection->hasConstant($constant->name) && $reflection->getConstant($constant->name)->getDeclaringClass()->getName() === $className) {
                    $requiredMembers[$constant->key] ??= false;

                    continue;
                }

                $requiredMembers[$constant->key] ??= $constant;
            }

            foreach ($nativeReflection->getProperties() as $reflectionProperty) {
                $property = AbstractMember::from($reflectionProperty, $requiredBy);

                if (! $property) {
                    continue;
                }

                if ($reflection->hasProperty($property->name) && $reflection->getProperty($property->name, $scope)->getDeclaringClass()->getName() === $className) {
                    $requiredMembers[$property->key] ??= false;

                    continue;
                }

                $requiredMembers[$property->key] ??= $property;
            }

            foreach ($nativeReflection->getMethods() as $reflectionMethod) {
                $method = AbstractMember::from($reflectionMethod, $requiredBy);

                if (! $method) {
                    continue;
                }

                if ($reflection->hasMethod($method->name) && $reflection->getMethod($method->name, $scope)->getDeclaringClass()->getName() === $className) {
                    $requiredMembers[$method->key] ??= false;

                    continue;
                }

                $requiredMembers[$method->key] ??= $method;
            }
        }

        return $requiredMembers;
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }
}
