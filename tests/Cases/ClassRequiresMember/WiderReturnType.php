<?php

declare(strict_types = 1);

namespace Tests\Cases\ClassRequiresMember;

final class WiderReturnType implements RequiresMethod
{
    public function run(): string|int
    {
        return 'ok';
    }
}
