<?php

declare(strict_types = 1);

namespace Tests\Cases\FinalTraitMethod;

trait SealedTrait
{
    final public function sealed(): string
    {
        return 'sealed';
    }
}
