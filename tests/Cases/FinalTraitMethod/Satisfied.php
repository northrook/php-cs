<?php

declare(strict_types = 1);

namespace Tests\Cases\FinalTraitMethod;

final class Satisfied
{
    use SealedTrait;

    public function other(): string
    {
        return 'ok';
    }
}
