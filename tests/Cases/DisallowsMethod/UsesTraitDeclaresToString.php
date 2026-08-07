<?php

declare(strict_types=1);

namespace Tests\Cases\DisallowsMethod;

class UsesTraitDeclaresToString
{
    use DisallowsToStringTrait;

    public function __toString(): string
    {
        return '';
    }
}
