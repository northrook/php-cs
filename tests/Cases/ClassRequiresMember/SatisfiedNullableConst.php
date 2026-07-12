<?php

declare(strict_types=1);

namespace Tests\Cases\ClassRequiresMember;

final class SatisfiedNullableConst implements Requires_Nullable_Const
{
    public const null|string NULLABLE_CONST = null;
}
