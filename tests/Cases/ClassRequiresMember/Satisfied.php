<?php

declare(strict_types = 1);

namespace Tests\Cases\ClassRequiresMember;

final class Satisfied implements RequiresConst, RequiresMethod
{
    public const string REQUIRED_CONST = 'ok';

    public function run(): string
    {
        return 'ok';
    }
}
