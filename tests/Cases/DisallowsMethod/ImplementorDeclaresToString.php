<?php

declare(strict_types=1);

namespace Tests\Cases\DisallowsMethod;

class ImplementorDeclaresToString implements DisallowsToStringInterface
{
    public function __toString(): string
    {
        return '';
    }
}
