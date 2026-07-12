<?php

declare(strict_types = 1);

namespace Tests\Cases\StaticClass;

class UsesStaticTraitPrivate
{
    use StaticTrait;

    private function __construct() {}
}
