<?php

declare(strict_types=1);

namespace Tests\Cases\FinalTraitMethod;

final class SubclassOverridesFinal extends UsesSealedTrait
{
    public function sealed(): string
    {
        return 'overridden';
    }
}
