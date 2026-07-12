<?php

declare(strict_types=1);

namespace Tests\Cases\StaticClass;

final class HostsAnonymous
{
    public function make(): object
    {
        return new class {};
    }
}
