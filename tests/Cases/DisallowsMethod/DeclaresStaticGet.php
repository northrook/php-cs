<?php

declare(strict_types=1);

namespace Tests\Cases\DisallowsMethod;

class DeclaresStaticGet extends DisallowsStaticGet
{
    public static function get(): void {}
}
