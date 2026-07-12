<?php

declare(strict_types=1);

namespace Tests\Cases\StaticClass;

class UsesNestedStaticTraitPrivate
{
    use UsesNestedStaticTrait;

    private function __construct() {}
}
