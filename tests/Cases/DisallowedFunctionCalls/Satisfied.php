<?php

declare(strict_types=1);

namespace Tests\Cases\DisallowedFunctionCalls;

final class Satisfied
{
    public function run(
        mixed $value,
    ): string {
        $fn = 'var_export';

        return \json_encode($value) . $fn($value) . $this->var_export($value);
    }

    public function var_export(
        mixed $value,
    ): string {
        return (string) $value;
    }
}
