<?php

declare(strict_types=1);

namespace Northrook\Dev\PHPStan;

use Northrook\Dev\PHPStan\Internal\ErrorHandler;
use Northrook\Dev\PHPStan\Internal\NodeResolver;
use Northrook\Dev\PHPStan\RequiresMemberRule\RequiredMemberCollector;
use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
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

        $className       = $this->resolveName($node);
        $reflection      = $this->reflectionProvider->getClass($className);
        $requiredMembers = RequiredMemberCollector::collect($reflection);

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

    public function getNodeType(): string
    {
        return Interface_::class;
    }
}
