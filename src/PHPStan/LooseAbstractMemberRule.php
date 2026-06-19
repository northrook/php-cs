<?php

declare(strict_types = 1);

namespace Northrook\Dev\PHPStan;

use Northrook\Dev\PHPStan\Internal\{ErrorHandler, NodeResolver};
use Northrook\Dev\PHPStan\MemberRules\AbstractMember;
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

    /** @var class-string */
    private string $className;

    private ClassReflection $reflection;

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
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node instanceof Class_) {
            return [];
        }

        $this->className  = $this->resolveName($node);
        $this->reflection = $this->reflectionProvider->getClass($this->className);

        foreach ($this->requiredMembers($scope) as $member) {
            if ($member === false) {
                continue;
            }

            $memberName = $member->name($this->className);
            $definition = $member->definition . ' ' . $memberName;

            $this->error(
                message: "Missing {$definition}.",
                identifier: 'abstractMember.notFound',
            )->tip($member->requiredBy);
        }

        return $this->errors();
    }

    /**
     * @return array<string, false|AbstractMember>
     *
     * @throws ShouldNotHappenException
     * @throws \PHPStan\Reflection\MissingConstantFromReflectionException
     * @throws \PHPStan\Reflection\MissingMethodFromReflectionException
     */
    private function requiredMembers(Scope $scope): array
    {
        $requiredMembers = [];

        foreach ([
            ...$this->reflection->getParents(),
            ...$this->reflection->getTraits(),
        ] as $node) {
            $requiredBy = $this->getNodeLabel($node);
            $reflection = $node->getNativeReflection();

            foreach ($reflection->getReflectionConstants() as $reflectionConstant) {
                $constant = AbstractMember::from($reflectionConstant, $requiredBy);

                if (! $constant) {
                    continue;
                }

                if (
                    $this->reflection->hasConstant($constant->name)
                    && $this->reflection->getConstant($constant->name)->getDeclaringClass()->getName()
                        === $this->className
                ) {
                    $requiredMembers[$constant->key] ??= false;

                    continue;
                }

                $requiredMembers[$constant->key] ??= $constant;
            }

            foreach ($reflection->getProperties() as $reflectionProperty) {
                $property = AbstractMember::from($reflectionProperty, $requiredBy);

                if (! $property) {
                    continue;
                }

                if (
                    $this->reflection->hasProperty($property->name)
                    && $this->reflection->getProperty($property->name, $scope)->getDeclaringClass()->getName()
                        === $this->className
                ) {
                    $requiredMembers[$property->key] ??= false;

                    continue;
                }

                $requiredMembers[$property->key] ??= $property;
            }

            foreach ($reflection->getMethods() as $reflectionMethod) {
                $method = AbstractMember::from($reflectionMethod, $requiredBy);

                if (! $method) {
                    continue;
                }

                if (
                    $this->reflection->hasMethod($method->name)
                    && $this->reflection->getMethod($method->name, $scope)->getDeclaringClass()->getName()
                        === $this->className
                ) {
                    $requiredMembers[$method->key] ??= false;

                    continue;
                }

                $requiredMembers[$method->key] ??= $method;
            }
        }

        return $requiredMembers;
    }

    private function getNodeLabel(ClassReflection $node): string
    {
        $fragments = [];

        if ($node->isAbstract()) {
            $fragments[] = 'abstract';
            $fragments[] = 'class';
        } elseif ($node->isTrait()) {
            $fragments[] = 'trait';
        } else {
            $fragments[] = 'class';
        }

        $fragments[] = $node->getName();

        return \implode(' ', $fragments);
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }
}
