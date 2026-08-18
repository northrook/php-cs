<?php

declare(strict_types=1);

namespace Tests\Cases\Initializer;

final class ChildInherits extends ParentWithInit
{
    public function __construct(
        string $value,
    ) {
        parent::__construct($value);
    }
}
