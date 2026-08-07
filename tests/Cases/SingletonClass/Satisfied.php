<?php

declare(strict_types=1);

namespace Tests\Cases\SingletonClass;

use Northrook\Contracts\Interfaces\SingletonInterface;

/**
 * @singleton
 */
class Satisfied implements SingletonInterface
{
    private static null|self $instance = null;

    private function __construct() {}

    public static function get(): static
    {
        return self::$instance ??= new self;
    }
}
