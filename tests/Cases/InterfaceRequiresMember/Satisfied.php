<?php

declare(strict_types = 1);

namespace Tests\Cases\InterfaceRequiresMember;

/**
 * @const BASIC_CONST
 */
interface Satisfied
{
    public const string BASIC_CONST = 'ok';
}
