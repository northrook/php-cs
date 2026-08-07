<?php

declare(strict_types=1);

namespace Tests\Cases\DisallowsMethod;

class Unconstrained
{
    public function __toString(): string
    {
        return '';
    }
}
