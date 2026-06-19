<?php

declare(strict_types = 1);

namespace Tests\Cases\LooseAbstractMember;

abstract class AbstractBase
{
    /** @abstract */
    public const string LABEL = 'base';

    /** @abstract */
    protected string $name = 'base';

    /** @abstract */
    public function label(): string
    {
        return self::LABEL;
    }
}
