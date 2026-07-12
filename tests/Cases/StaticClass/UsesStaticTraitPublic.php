<?php

declare(strict_types = 1);

namespace Tests\Cases\StaticClass;

class UsesStaticTraitPublic
{
    use StaticTrait;

    public function __construct() {}
}
