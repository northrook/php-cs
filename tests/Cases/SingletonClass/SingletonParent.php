<?php

declare(strict_types=1);

namespace Tests\Cases\SingletonClass;

use Northrook\Contracts\Singleton;

/**
 * @singleton
 */
abstract class SingletonParent extends Singleton {}
