<?php

declare(strict_types=1);

namespace Tests\Cases\StaticClass;

class SubclassPublicConstructor extends StaticParent
{
    public function __construct()
    {
        parent::__construct();
    }
}
