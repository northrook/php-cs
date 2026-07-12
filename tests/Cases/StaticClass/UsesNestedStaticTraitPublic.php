<?php

declare(strict_types=1);

namespace Tests\Cases\StaticClass;

class UsesNestedStaticTraitPublic
{
    use UsesNestedStaticTrait;

    public function __construct() {}
}
