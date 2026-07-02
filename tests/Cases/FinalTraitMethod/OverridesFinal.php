<?php

declare(strict_types = 1);

namespace Tests\Cases\FinalTraitMethod;

final class OverridesFinal
{
    use SealedTrait;

    public function sealed(): string
    {
        return 'overridden';
    }
}
