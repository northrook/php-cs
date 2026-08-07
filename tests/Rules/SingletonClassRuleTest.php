<?php

declare(strict_types=1);

namespace Tests\Rules;

use Northrook\PHPStan\SingletonClassRule;
use PHPStan\Rules\Rule;
use Tests\PHPStanRuleTest;

/**
 * @extends PHPStanRuleTest<SingletonClassRule>
 */
final class SingletonClassRuleTest extends PHPStanRuleTest
{
    public function testReportsMissingBase(): void
    {
        $this->expect(__DIR__ . '/../Cases/SingletonClass/MissingBase.php', [
            [
                'Class Tests\Cases\SingletonClass\MissingBase is @singleton but does not extend Northrook\Contracts\Singleton.',
                10,
                'Imposed by class Tests\Cases\SingletonClass\MissingBase.',
            ],
        ]);
    }

    public function testPassesWhenExtendingSingleton(): void
    {
        $this->expect(__DIR__ . '/../Cases/SingletonClass/Satisfied.php', []);
    }

    public function testIgnoresUntaggedClass(): void
    {
        $this->expect(__DIR__ . '/../Cases/SingletonClass/Untagged.php', []);
    }

    public function testPassesSubclassOfTaggedSingletonParent(): void
    {
        $this->expect(__DIR__ . '/../Cases/SingletonClass/SubclassSatisfied.php', []);
    }

    public function testReportsSubclassOfTaggedNonSingletonParent(): void
    {
        $this->expect(__DIR__ . '/../Cases/SingletonClass/SubclassMissingBase.php', [
            [
                'Class Tests\Cases\SingletonClass\SubclassMissingBase is @singleton but does not extend Northrook\Contracts\Singleton.',
                7,
                'Imposed by abstract class Tests\Cases\SingletonClass\BadSingletonParent.',
            ],
        ]);
    }

    public function testReportsTraitImposedMissingBase(): void
    {
        $this->expect(__DIR__ . '/../Cases/SingletonClass/UsesSingletonTraitMissing.php', [
            [
                'Class Tests\Cases\SingletonClass\UsesSingletonTraitMissing is @singleton but does not extend Northrook\Contracts\Singleton.',
                7,
                'Imposed by trait Tests\Cases\SingletonClass\SingletonTrait.',
            ],
        ]);
    }

    public function testPassesTraitImposedWhenExtendingSingleton(): void
    {
        $this->expect(__DIR__ . '/../Cases/SingletonClass/UsesSingletonTraitSatisfied.php', []);
    }

    public function testReportsNestedTraitImposedMissingBase(): void
    {
        $this->expect(__DIR__ . '/../Cases/SingletonClass/UsesNestedSingletonTraitMissing.php', [
            [
                'Class Tests\Cases\SingletonClass\UsesNestedSingletonTraitMissing is @singleton but does not extend Northrook\Contracts\Singleton.',
                7,
                'Imposed by trait Tests\Cases\SingletonClass\NestedSingletonTrait.',
            ],
        ]);
    }

    protected function getRule(): Rule
    {
        return new SingletonClassRule($this->createReflectionProvider());
    }
}
