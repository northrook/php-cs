<?php

declare(strict_types=1);

namespace Tests\Cases\DisallowsMethod;

/**
 * Inherits __toString from HasToString while composing a @disallows trait.
 */
class InheritsWhileDisallowed extends HasToString
{
    use DisallowsToStringTrait;
}
