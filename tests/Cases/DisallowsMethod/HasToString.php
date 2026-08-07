<?php

declare(strict_types=1);

namespace Tests\Cases\DisallowsMethod;

class HasToString
{
    public function __toString(): string
    {
        return '';
    }
}
