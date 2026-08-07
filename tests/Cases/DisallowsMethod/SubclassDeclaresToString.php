<?php

declare(strict_types=1);

namespace Tests\Cases\DisallowsMethod;

class SubclassDeclaresToString extends DisallowToString
{
    public function __toString(): string
    {
        return '';
    }
}
