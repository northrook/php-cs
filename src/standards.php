<?php

declare( strict_types = 1 );

namespace Northrook;

use PhpCsFixer\{Config, Finder};

function standards( Finder $finder, array $rules = [] ) : Config
{
    $config = new Config( 'northrook' );

    $config->setFinder( $finder )
           ->setRules( \array_merge( require __DIR__ . '/rules.php', $rules ) )
           ->setRiskyAllowed( true );

    return $config;
}