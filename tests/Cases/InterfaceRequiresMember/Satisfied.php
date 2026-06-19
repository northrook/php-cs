<?php

declare(strict_types = 1);

namespace Tests\Cases\InterfaceRequiresMember;

/**
 * @requires-const BASIC_CONST
 */
interface Satisfied
{
    public const string BASIC_CONST = 'ok';
}
