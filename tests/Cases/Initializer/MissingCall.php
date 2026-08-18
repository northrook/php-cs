<?php

declare(strict_types=1);

namespace Tests\Cases\Initializer;

final class MissingCall
{
    use InitTrait;

    public function __construct(
        string $value,
    ) {
        // initializer.notCalledFromConstructor
    }
}
