<?php

declare(strict_types=1);

namespace Tests\Cases\DisallowedFunctionCalls;

function disallowed_helper(
    mixed $value,
): string {
    return 'namespaced';
}

final class NamespacedBanned
{
    public function run(
        mixed $value,
    ): string {
        return disallowed_helper($value);
    }
}
