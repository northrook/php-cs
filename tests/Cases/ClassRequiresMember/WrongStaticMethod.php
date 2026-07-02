<?php

declare(strict_types=1);

namespace Tests\Cases\ClassRequiresMember;

final class WrongStaticMethod implements RequiresStaticMethod
{
    public function register(): void {}
}
