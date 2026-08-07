<?php

declare(strict_types=1);

namespace Northrook\PHPStan;

use Northrook\PHPStan\Internal\{ErrorHandler, NodeResolver};
use Northrook\PHPStan\RequiresMemberRule\{RequiredMember, RequiredMemberCollector};
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\{Scope};
use PHPStan\Reflection\{ClassReflection, ReflectionProvider};
use PHPStan\Rules\{Rule, RuleError};
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<Class_>
 */
final class ClassRequiresMemberRule implements Rule
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
     * @param Node   $node
     * @param Scope  $scope
     *
     * @return array<array-key,RuleError>
     * @throws ShouldNotHappenException
     */
    public function processNode(
        Node  $node,
        Scope $scope,
    ): array {
        if ($this->skipInvalidNode($node)) {
            return [];
        }

        foreach ($this->requiredMembers() as $member) {
            $member->reflect($this->reflection, $scope);
            $memberName = $member->name(
                $this->className,
            );
            $definition = $member->label . ' ' . $memberName;
            $requiredBy = $member->label . ' required by ' . $member->requiredBy . '.';

            if ($member->notDeclared()) {
                $this->error(
                    message   : "Missing {$definition}.",
                    identifier: 'requiresMember.notFound',
                )->tip(
                    $requiredBy,
                );

                continue;
            }

            if ($modifiers = $member->missingModifiers()) {
                $ignorable = $modifiers->missing === [] && $modifiers->unexpected !== [];

                $this->error(
                    message   : "{$definition} {$modifiers->required()} modifiers.",
                    identifier: "requiresMember.{$member->type}.Modifiers",
                    ignorable : $ignorable,
                );

                if ($modifiers->missing) {
                    $this->error(
                        message   : "{$definition} missing {$modifiers->missing()}",
                        identifier: "requiresMember.{$member->type}.ModifiersMissing",
                    );
                }

                if ($modifiers->unexpected && $modifiers->missing === []) {
                    $this->error(
                        message   : "{$definition} unexpected modifiers {$modifiers->unexpected()}",
                        identifier: "requiresMember.{$member->type}.ModifiersUnexpected",
                        ignorable : true,
                    );
                }

                if ($modifiers->declared === []) {
                    $this->error(
                        message   : "{$definition} has no declared modifiers.",
                        identifier: "requiresMember.{$member->type}.Modifiers",
                        ignorable : $ignorable,
                    );
                } else {
                    $this->error(
                        message   : "{$definition} has {$modifiers->declared()} modifiers.",
                        identifier: "requiresMember.{$member->type}.Modifiers",
                        ignorable : $ignorable,
                    );
                }
            }

            if ($typeOf = $member->missingTypeDeclarations()) {
                $ignorable = $typeOf->missing === [] && $typeOf->unexpected !== [];

                $this->error(
                    message   : "{$definition} requires types {$typeOf->required()}",
                    identifier: "requiresMember.{$member->type}.RequiresType",
                    ignorable : $ignorable,
                );

                if ($typeOf->missing) {
                    $this->error(
                        message   : "{$definition} missing {$typeOf->missing()}",
                        identifier: "requiresMember.{$member->type}.TypeMissing",
                    );
                }

                if ($typeOf->unexpected && $typeOf->missing === []) {
                    $this->error(
                        message   : "{$definition} unexpected types {$typeOf->unexpected()}",
                        identifier: "requiresMember.{$member->type}.UnexpectedType",
                        ignorable : true,
                    );
                }

                if ($typeOf->declared === []) {
                    $this->error(
                        message   : "{$definition} has no declared types.",
                        identifier: "requiresMember.{$member->type}.UndeclaredType",
                        ignorable : $ignorable,
                    );
                } else {
                    $this->error(
                        message   : "{$definition} has {$typeOf->declared()} types.",
                        identifier: "requiresMember.{$member->type}.DeclaredType",
                        ignorable : $ignorable,
                    );
                }
            }
        }

        return $this->errors();
    }

    /**
     * @return array<string, RequiredMember>
     * @throws ShouldNotHappenException
     */
    private function requiredMembers(): array
    {
        $requiredMembers = [];

        foreach ([
            $this->reflection,
            ...$this->reflection->getInterfaces(),
            ...$this->reflection->getParents(),
            ...$this->reflection->getTraits(true),
        ] as $source) {
            foreach (RequiredMemberCollector::collect($source) as $member) {
                $requiredMembers[$member->key()] = $member;
            }
        }

        return $requiredMembers;
    }

    /**
     * @param Node  $node
     *
     * @return bool
     * @throws ShouldNotHappenException
     */
    private function skipInvalidNode(
        Node $node,
    ): bool {
        if (! $node instanceof Class_ || $node->isAnonymous() || $node->isAbstract()) {
            return true;
        }

        $this->className  = $this->resolveName($node);
        $this->reflection = $this->reflectionProvider->getClass($this->className);

        return false;
    }

    final public function getNodeType(): string
    {
        return Class_::class;
    }
}
