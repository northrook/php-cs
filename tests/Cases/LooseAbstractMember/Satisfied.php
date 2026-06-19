<?php

declare(strict_types = 1);

namespace Tests\Cases\LooseAbstractMember;

final class Satisfied extends AbstractBase
{
    public const string LABEL = 'child';

    protected string $name = 'child';

    public function label(): string
    {
        return self::LABEL;
    }
}
