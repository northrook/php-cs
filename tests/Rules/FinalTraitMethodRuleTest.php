<?php

declare(strict_types = 1);

namespace Tests\Rules;

use Northrook\PHPStan\FinalTraitMethodRule;
use PHPStan\Rules\Rule;
use Tests\PHPStanRuleTest;

/**
 * @extends PHPStanRuleTest<FinalTraitMethodRule>
 */
final class FinalTraitMethodRuleTest extends PHPStanRuleTest
{
    public function testReportsClassOverridingFinalTraitMethod(): void
    {
        $this->expect(__DIR__ . '/../Cases/FinalTraitMethod/OverridesFinal.php', [
            [
                'Method Tests\Cases\FinalTraitMethod\OverridesFinal::sealed() overrides final method sealed by trait Tests\Cases\FinalTraitMethod\SealedTrait.',
                11,
                'PHP does not enforce a trait\'s `final` on the using class, silently breaking the seal.',
            ],
        ]);
    }

    public function testReportsTraitOverridingFinalTraitMethod(): void
    {
        $this->expect(__DIR__ . '/../Cases/FinalTraitMethod/TraitOverridesFinal.php', [
            [
                'Method Tests\Cases\FinalTraitMethod\TraitOverridesFinal::sealed() overrides final method sealed by trait Tests\Cases\FinalTraitMethod\SealedTrait.',
                11,
                'PHP does not enforce a trait\'s `final` on the using class, silently breaking the seal.',
            ],
        ]);
    }

    public function testPassesWhenFinalTraitMethodIsNotOverridden(): void
    {
        $this->expect(__DIR__ . '/../Cases/FinalTraitMethod/Satisfied.php', []);
    }

    protected function getRule(): Rule
    {
        return new FinalTraitMethodRule($this->createReflectionProvider());
    }
}
