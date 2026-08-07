<?php

declare(strict_types=1);

namespace Tests\Cases\DisallowsMethod;

class DeclaresInstanceGet extends DisallowsStaticGet
{
    public function get(): void {}
}
