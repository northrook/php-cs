<?php

namespace Northrook\PHPStan;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\PhpDoc\{PhpDocStringResolver};
use PHPStan\Reflection\{ReflectionProvider};

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
     */
    public function processNode( Node $node, Scope $scope ) : array
    {
        if ( ! isset( $node->namespacedName ) ) {
            return [];
        }

        $className       = (string) $node->namespacedName;
        $classReflection = $this->reflectionProvider->getClass( $className );

        $doc    = $node->getDocComment()?->getText() ?? '';
        $phpDoc = $this->phpDocResolver->resolve( $doc, $scope->getFile(), null, null );

        $tags   = $phpDoc->getTagsByName( '@phpstan-requires-method' );
        $errors = [];

        foreach ( $tags as $tag ) {
            $content                   = \trim( (string) $tag->value );
            [$returnType, $methodName] = \explode( ' ', $content, 2 );

            if ( ! $classReflection->hasMethod( $methodName ) ) {
                $errors[] = "Class {$className} is missing required method `{$methodName}`.";
            }
        }

        return $errors;
    }
}
