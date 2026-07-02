<?php

declare(strict_types = 1);

namespace Tests\Cases\FinalTraitMethod;

trait TraitOverridesFinal
{
    use SealedTrait;

    public function sealed(): string
    {
        return 'overridden';
    }
}
