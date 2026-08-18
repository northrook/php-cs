<?php

declare(strict_types=1);

namespace Tests\Cases\Initializer;

final class Satisfied
{
    use InitTrait;

    public function __construct(
        string $value,
    ) {
        $this->initializeValue($value);
    }
}
