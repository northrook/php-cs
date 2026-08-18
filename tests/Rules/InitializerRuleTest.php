<?php

declare(strict_types=1);

namespace Tests\Rules;

use Northrook\PHPStan\InitializerRule;
use PHPStan\Rules\Rule;
use Tests\Support\PHPStanRuleTest;

/**
 * @extends \Tests\Support\PHPStanRuleTest<InitializerRule>
 */
final class InitializerRuleTest extends PHPStanRuleTest
{
    public function testPassesWhenConstructorCallsTaggedMethod(): void
    {
        $this->expect(__DIR__ . '/../Cases/Initializer/Satisfied.php', []);
    }

    public function testReportsMissingConstructorCall(): void
    {
        $this->expect(__DIR__ . '/../Cases/Initializer/MissingCall.php', [
            [
                'Method Tests\Cases\Initializer\MissingCall::__construct() must call @initializer method initializeValue().',
                7,
                'Introduced by trait Tests\Cases\Initializer\InitTrait::initializeValue().',
            ],
        ]);
    }

    public function testReportsWhenClassHasNoConstructor(): void
    {
        $this->expect(__DIR__ . '/../Cases/Initializer/NoConstructor.php', [
            [
                'Method Tests\Cases\Initializer\NoConstructor::__construct() must call @initializer method initializeValue().',
                7,
                'Introduced by trait Tests\Cases\Initializer\InitTrait::initializeValue().',
            ],
        ]);
    }

    public function testReportsCallOutsideConstructor(): void
    {
        $this->expect(__DIR__ . '/../Cases/Initializer/CalledOutsideConstructor.php', [
            [
                'Method Tests\Cases\Initializer\CalledOutsideConstructor::initializeValue() is @initializer and must only be called from __construct or another @initializer method.',
                21,
                'Tagged by trait Tests\Cases\Initializer\InitTrait::initializeValue().',
            ],
        ]);
    }

    public function testPassesWhenChildInheritsTaggedMethodFromParent(): void
    {
        $this->expect(__DIR__ . '/../Cases/Initializer/ChildInherits.php', []);
    }

    public function testReportsCallFromClosureInConstructor(): void
    {
        $this->expect(__DIR__ . '/../Cases/Initializer/CalledFromClosure.php', [
            [
                'Method Tests\Cases\Initializer\CalledFromClosure::__construct() must call @initializer method initializeValue().',
                7,
                'Introduced by trait Tests\Cases\Initializer\InitTrait::initializeValue().',
            ],
        ]);
    }

    public function testReportsStaticTaggedMethod(): void
    {
        $this->expect(__DIR__ . '/../Cases/Initializer/StaticMethod.php', [
            [
                'Static method Tests\Cases\Initializer\StaticMethod::initialize() must not be tagged @initializer.',
                7,
                'Tagged by class Tests\Cases\Initializer\StaticMethod::initialize().',
            ],
        ]);
    }

    public function testReportsRedundantTagOnConstruct(): void
    {
        $this->expect(__DIR__ . '/../Cases/Initializer/RedundantTag.php', [
            [
                'Method Tests\Cases\Initializer\RedundantTag::__construct() must not be tagged @initializer.',
                7,
            ],
        ]);
    }

    public function testIgnoresUntaggedClass(): void
    {
        $this->expect(__DIR__ . '/../Cases/Initializer/Untagged.php', []);
    }

    protected function getRule(): Rule
    {
        return new InitializerRule($this->createReflectionProvider());
    }
}
