<?php

declare(strict_types=1);

namespace Tests\Cases\Initializer;

final class CalledOutsideConstructor
{
    use InitTrait;

    public function __construct(
        string $value,
    ) {
        $this->initializeValue($value);
    }

    public function reset(
        string $value,
    ): void {
        // initializer.calledOutsideConstructor
        $this->initializeValue($value);
    }
}
