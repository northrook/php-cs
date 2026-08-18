<?php

declare(strict_types=1);

namespace Tests\Rules;

use Northrook\PHPStan\StaticClassRule;
use PHPStan\Rules\Rule;
use Tests\Support\PHPStanRuleTest;

/**
 * @extends \Tests\Support\PHPStanRuleTest<StaticClassRule>
 */
final class StaticClassRuleTest extends PHPStanRuleTest
{
    public function testReportsPublicConstructor(): void
    {
        $this->expect(__DIR__ . '/../Cases/StaticClass/PublicConstructor.php', [
            [
                'Class Tests\Cases\StaticClass\PublicConstructor is @static but has a public constructor.',
                10,
                'Imposed by class Tests\Cases\StaticClass\PublicConstructor.',
            ],
        ]);
    }

    public function testReportsMissingConstructor(): void
    {
        $this->expect(__DIR__ . '/../Cases/StaticClass/MissingConstructor.php', [
            [
                'Class Tests\Cases\StaticClass\MissingConstructor is @static but has no constructor.',
                10,
                'Imposed by class Tests\Cases\StaticClass\MissingConstructor.',
            ],
        ]);
    }

    public function testPassesPrivateConstructor(): void
    {
        $this->expect(__DIR__ . '/../Cases/StaticClass/PrivateConstructor.php', []);
    }

    public function testPassesProtectedConstructor(): void
    {
        $this->expect(__DIR__ . '/../Cases/StaticClass/ProtectedConstructor.php', []);
    }

    public function testReportsSubclassPublicConstructor(): void
    {
        $this->expect(__DIR__ . '/../Cases/StaticClass/SubclassPublicConstructor.php', [
            [
                'Class Tests\Cases\StaticClass\SubclassPublicConstructor is @static but has a public constructor.',
                7,
                'Imposed by class Tests\Cases\StaticClass\StaticParent.',
            ],
        ]);
    }

    public function testPassesSubclassProtectedConstructor(): void
    {
        $this->expect(__DIR__ . '/../Cases/StaticClass/SubclassProtectedConstructor.php', []);
    }

    public function testReportsTraitImposedPublicConstructor(): void
    {
        $this->expect(__DIR__ . '/../Cases/StaticClass/UsesStaticTraitPublic.php', [
            [
                'Class Tests\Cases\StaticClass\UsesStaticTraitPublic is @static but has a public constructor.',
                7,
                'Imposed by trait Tests\Cases\StaticClass\StaticTrait.',
            ],
        ]);
    }

    public function testPassesTraitImposedPrivateConstructor(): void
    {
        $this->expect(__DIR__ . '/../Cases/StaticClass/UsesStaticTraitPrivate.php', []);
    }

    public function testReportsNestedTraitImposedPublicConstructor(): void
    {
        $this->expect(__DIR__ . '/../Cases/StaticClass/UsesNestedStaticTraitPublic.php', [
            [
                'Class Tests\Cases\StaticClass\UsesNestedStaticTraitPublic is @static but has a public constructor.',
                7,
                'Imposed by trait Tests\Cases\StaticClass\NestedStaticTrait.',
            ],
        ]);
    }

    public function testPassesNestedTraitImposedPrivateConstructor(): void
    {
        $this->expect(__DIR__ . '/../Cases/StaticClass/UsesNestedStaticTraitPrivate.php', []);
    }

    public function testIgnoresUnconstrainedClass(): void
    {
        $this->expect(__DIR__ . '/../Cases/StaticClass/Unconstrained.php', []);
    }

    public function testIgnoresAnonymousClass(): void
    {
        $this->expect(__DIR__ . '/../Cases/StaticClass/HostsAnonymous.php', []);
    }

    protected function getRule(): Rule
    {
        return new StaticClassRule($this->createReflectionProvider());
    }
}
