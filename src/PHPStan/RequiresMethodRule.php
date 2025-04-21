<?php

namespace Northrook\PHPStan;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\{Rule, RuleErrorBuilder};

class RequiresMethodRule implements Rule
{
    public function __construct(
        private ReflectionProvider   $reflectionProvider,
        private PhpDocStringResolver $phpDocResolver,
    ) {}

    public function getNodeType() : string
    {
        return Node\Stmt\Class_::class;
    }

    /**
     * @param Node  $node
     * @param Scope $scope
     *
     * @return array|\PHPStan\Rules\IdentifierRuleError[]
     * @throws \PHPStan\ShouldNotHappenException
     */
    public function processNode( Node $node, Scope $scope ) : array
    {
        if ( ! isset( $node->namespacedName ) ) {
            return [];
        }

        $className       = (string) $node->namespacedName;
        $classReflection = $this->reflectionProvider->getClass( $className );

        if ( $classReflection->isAbstract() ) {
            return [];
        }

        $doc     = $classReflection->getResolvedPhpDoc();
        $comment = false;

        if ( ! $doc ) {
            foreach ( $classReflection->getParents() as $parent ) {
                $parentDoc = $parent->getResolvedPhpDoc();
                if ( ! $parentDoc ) {
                    continue;
                }

                $comment = $parentDoc->getPhpDocString();
                if ( ! $comment || ! \str_contains( $comment, '@require-method' ) ) {
                    continue;
                }
            }
        }
        else {
            $comment = $doc->getPhpDocString();
        }

        if ( ! $comment || ! \str_contains( $comment, '@require-method' ) ) {
            return [];
        }

        $phpDoc = $this->phpDocResolver->resolve(
            $comment,
            $scope->getFile(),
            null,
            null,
        );

        $requiresMethod = \trim( $phpDoc->getTagsByName( '@require-method' )[0]->value );

        if ( \str_contains( $requiresMethod, ' ' ) ) {
            [$returnType, $methodName] = \explode( ' ', $requiresMethod, 2 );
        }
        else {
            $returnType = null;
            $methodName = $requiresMethod;
        }

        $methodName = \strstr( $methodName, '(', true ) ?: $methodName;

        if ( ! $classReflection->hasMethod( $methodName ) ) {
            return [
                RuleErrorBuilder::message( "Class {$className} is missing required method `{$methodName}`." )
                    ->identifier( 'northrook.class.requires.method' )
                    ->build(),
            ];
        }
        return [];
    }
}
