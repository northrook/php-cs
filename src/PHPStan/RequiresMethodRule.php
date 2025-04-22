<?php

namespace Northrook\PHPStan;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\{Rule, RuleErrorBuilder};

readonly class RequiresMethodRule implements Rule
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

        $doc      = $classReflection->getResolvedPhpDoc();
        $comment  = $doc?->getPhpDocString() ?? null;
        $required = [];

        if ( $comment && \str_contains( $comment, '@require-method' ) ) {
            $required[] = $comment;
        }

        foreach ( $classReflection->getParents() as $parent ) {
            $parentDoc = $parent->getResolvedPhpDoc();
            if ( ! $parentDoc ) {
                continue;
            }

            $comment = $parentDoc->getPhpDocString();
            if ( $comment && \str_contains( $comment, '@require-method' ) ) {
                $required[] = $comment;
            }
        }

        $errors = [];

        foreach ( $required as $requiredDoc ) {
            $phpDoc         = $this->phpDocResolver->resolve( $requiredDoc );
            $docNode        = $phpDoc->getTagsByName( '@require-method' );
            $requiresMethod = \trim( \array_pop( $docNode )->value );

            if ( \str_contains( $requiresMethod, ' ' ) ) {
                [$returnType, $methodName] = \explode( ' ', $requiresMethod, 2 );
            }
            else {
                $returnType = null;
                $methodName = $requiresMethod;
            }
            $methodName = \strstr( $methodName, '(', true ) ?: '$methodName';

            if ( $classReflection->hasMethod( $methodName ) || \array_key_exists( $methodName, $errors ) ) {
                continue;
            }
            $error = RuleErrorBuilder::message(
                "Method {$methodName}() is required by {$className}.",
            )
                ->identifier( 'requireMethod.notFound' )
                ->nonIgnorable()
                ->build();

            $errors[$methodName] = $error;
        }

        return $errors;
    }
}
