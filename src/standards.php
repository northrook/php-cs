<?php

declare( strict_types = 1 );

namespace Northrook;

use PhpCsFixer\{Config, Finder};

/**
 * @param string|string[]  $in  `__DIR__`
 * @param string|string[]  $exclude
 * @param array            $rules
 *
 * @return \PhpCsFixer\Config
 */
function standards(
        string | array $in,
        array          $rules = [],
        string | array $exclude = [ 'vendor', 'var', 'tests', ],
) : Config
{
    $finder = Finder::create()
                    ->in( $in )
                    ->exclude( $exclude );

    $config = new Config( 'northrook' );

    $rules = \array_merge( require __DIR__ . '/rules.php', $rules );

    return $config->setFinder( $finder )
                  ->setRules( $rules )
                  ->setRiskyAllowed( true );
}