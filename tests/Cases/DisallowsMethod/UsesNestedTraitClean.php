<?php

declare(strict_types=1);

namespace Tests\Cases\DisallowsMethod;

class UsesNestedTraitClean
{
    use NestedDisallowsToString;
}
