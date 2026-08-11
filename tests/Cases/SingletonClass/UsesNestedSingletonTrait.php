<?php

declare(strict_types=1);

namespace Tests\Cases\SingletonClass;

trait UsesNestedSingletonTrait
{
    use NestedSingletonTrait;
}
