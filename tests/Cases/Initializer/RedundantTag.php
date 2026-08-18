<?php

declare(strict_types=1);

namespace Tests\Cases\Initializer;

final class RedundantTag
{
    /**
     * @initializer
     */
    public function __construct() {}
}
