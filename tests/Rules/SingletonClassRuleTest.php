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
    public function testReportsMissingInterface(): void
    {
        $this->expect(__DIR__ . '/../Cases/SingletonClass/MissingInterface.php', [
            [
                'Class Tests\Cases\SingletonClass\MissingInterface is @singleton but does not implement Northrook\Contracts\Interfaces\SingletonInterface.',
                10,
                'Extend Northrook\Contracts\Singleton or implement the interface directly.',
            ],
        ]);
    }

    public function testPassesWhenInterfaceImplemented(): void
    {
        $this->expect(__DIR__ . '/../Cases/SingletonClass/Satisfied.php', []);
    }

    public function testIgnoresUntaggedClass(): void
    {
        $this->expect(__DIR__ . '/../Cases/SingletonClass/Untagged.php', []);
    }

    protected function getRule(): Rule
    {
        return new SingletonClassRule($this->createReflectionProvider());
    }
}
