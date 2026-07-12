<?php

declare(strict_types=1);

namespace Tests\Cases\StaticClass;

class SubclassProtectedConstructor extends StaticParent
{
    protected function __construct()
    {
        parent::__construct();
    }
}
