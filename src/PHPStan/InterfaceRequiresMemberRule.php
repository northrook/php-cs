<?php

declare(strict_types = 1);

namespace Northrook\Dev\PHPStan;

use Northrook\Dev\PHPStan\Internal\{ErrorHandler, NodeResolver};
use Northrook\Dev\PHPStan\RequiresMemberRule\RequiredMember;
use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\{Rule, RuleError};
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<Interface_>
 */
final class InterfaceRequiresMemberRule implements Rule
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
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node instanceof Interface_) {
            return [];
        }

        $docBlock = $node->getDocComment();

        if ($docBlock === null || ! \str_contains($docBlock->getText(), '@requires-')) {
            return [];
        }

        $className       = $this->resolveName($node);
        $reflection      = $this->reflectionProvider->getClass($className);
        $requiredMembers = $this->requiredMembers($docBlock->getText(), $className);

        if ($requiredMembers === []) {
            return [];
        }

        foreach ($requiredMembers as $member) {
            $member->reflect($reflection, $scope);
            $memberName = $member->name($className);
            $definition = $member->label . ' ' . $memberName;
            $requiredBy = $member->label . ' required by ' . $member->requiredBy . '.';

            if ($member->notDeclared()) {
                $this->error(message: "Missing {$definition}.", identifier: 'requiresMember.notFound')->tip(
                    $requiredBy,
                );
            }
        }

        return $this->errors();
    }

    /**
     * @param class-string $className
     *
     * @return array<string, RequiredMember>
     */
    private function requiredMembers(string $docBlockString, string $className): array
    {
        $requiredMembers = [];

        foreach ($this->explodeDocBlock($docBlockString) as $phpTagString) {
            if (! \str_contains($phpTagString, '@requires-')) {
                continue;
            }

            $requiresMember = RequiredMember::from($phpTagString, 'Interface', $className);

            $requiredMembers[$requiresMember->key()] = $requiresMember;
        }

        return $requiredMembers;
    }

    public function getNodeType(): string
    {
        return Interface_::class;
    }
}
