<?php

declare(strict_types=1);

namespace Tests\Cases\Initializer;

final class StaticMethod
{
    /**
     * @initializer
     */
    protected static function initialize(): void {}
}
