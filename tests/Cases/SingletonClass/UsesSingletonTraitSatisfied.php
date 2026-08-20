<?php

declare(strict_types=1);

namespace Tests\Cases\SingletonClass;

use Northrook\Singleton;

class UsesSingletonTraitSatisfied extends Singleton
{
    use SingletonTrait;
}
