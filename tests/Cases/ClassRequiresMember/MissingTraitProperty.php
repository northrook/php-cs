<?php

declare(strict_types = 1);

namespace Tests\Cases\ClassRequiresMember;

final class MissingTraitProperty
{
    use RequiresIdTrait;
}
