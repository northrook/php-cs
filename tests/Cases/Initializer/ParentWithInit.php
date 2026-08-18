<?php

declare(strict_types=1);

namespace Tests\Cases\Initializer;

class ParentWithInit
{
    use InitTrait;

    public function __construct(
        string $value,
    ) {
        $this->initializeValue($value);
    }
}
