<?php

declare(strict_types = 1);

namespace Tests\Cases\ClassRequiresMember;

final class WrongReturnType implements RequiresMethod
{
    public function run(): int
    {
        return 1;
    }
}
