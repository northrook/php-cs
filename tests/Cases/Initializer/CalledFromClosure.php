<?php

declare(strict_types=1);

namespace Tests\Cases\Initializer;

final class CalledFromClosure
{
    use InitTrait;

    public function __construct(
        string $value,
    ) {
        $init = function() use ($value): void {
            // initializer.notCalledFromConstructor (closure call doesn't count)
            $this->initializeValue($value);
        };

        $init();
    }
}
