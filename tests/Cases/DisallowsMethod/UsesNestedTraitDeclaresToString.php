<?php

declare(strict_types=1);

namespace Tests\Cases\DisallowsMethod;

class UsesNestedTraitDeclaresToString
{
    use NestedDisallowsToString;

    public function __toString(): string
    {
        return '';
    }
}
