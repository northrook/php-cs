<?php

declare(strict_types=1);

namespace Northrook;

use PhpCsFixer\{Config, ConfigInterface, Finder};
use ErickSkrauch\PhpCsFixer\Fixers;

/**
 * @param string|string[]                                                                        $in      `__DIR__`
 * @param array<string, array<array-key, array<array-key, bool|string>|bool|string>|bool|string> $rules
 * @param string|string[]                                                                        $exclude
 *
 * @return ConfigInterface
 */
function standards(
    string|array $in,
    array        $rules = [],
    string|array $exclude = ['vendor', 'var', 'tests'],
) : ConfigInterface {
    $finder = Finder::create()
        ->in( $in )
        ->exclude( $exclude );

    $config = new Config( 'northrook' );

    $config->registerCustomFixers( new Fixers() );

    $rules = \array_merge( require __DIR__.'/rules.php', $rules );

    return $config->setFinder( $finder )
        ->setRules( $rules )
        ->setRiskyAllowed( true );
}
