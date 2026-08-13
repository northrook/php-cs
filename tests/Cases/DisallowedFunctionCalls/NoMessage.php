<?php

declare(strict_types=1);

namespace Tests\Cases\DisallowedFunctionCalls;

final class NoMessage
{
    public function run(
        mixed $value,
    ): string {
        return \var_export($value, true);
    }
}
