<?php

declare(strict_types=1);

namespace Tests\Cases\Initializer;

trait InitTrait
{
    protected readonly string $value;

    /**
     * @initializer
     */
    protected function initializeValue(
        string $value,
    ): void {
        $this->value = $value;
    }
}
