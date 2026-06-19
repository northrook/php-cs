<?php

declare(strict_types = 1);

namespace Tests\Rules;

use Northrook\Dev\PHPStan\ClassRequiresMemberRule;
use PHPStan\Rules\Rule;
use Tests\PHPStanRuleTest;

/**
 * @extends PHPStanRuleTest<ClassRequiresMemberRule>
 */
final class ClassRequiresMemberRuleTest extends PHPStanRuleTest
{
    public function testReportsMissingConstOnConcreteClass(): void
    {
        $this->expect(__DIR__ . '/../Cases/ClassRequiresMember/MissingConst.php', [
            [
                'Missing Constant Tests\Cases\ClassRequiresMember\MissingConst::REQUIRED_CONST.',
                7,
                'Constant required by Interface Tests\Cases\ClassRequiresMember\RequiresConst.',
            ],
        ]);
    }

    public function testReportsMissingMethodOnConcreteClass(): void
    {
        $this->expect(__DIR__ . '/../Cases/ClassRequiresMember/MissingMethod.php', [
            [
                'Missing Method Tests\Cases\ClassRequiresMember\MissingMethod->run.',
                7,
                'Method required by Interface Tests\Cases\ClassRequiresMember\RequiresMethod.',
            ],
        ]);
    }

    public function testReportsMissingPropertyOnConcreteClass(): void
    {
        $this->expect(__DIR__ . '/../Cases/ClassRequiresMember/MissingProperty.php', [
            [
                'Missing Property Tests\Cases\ClassRequiresMember\MissingProperty->label.',
                7,
                'Property required by Interface Tests\Cases\ClassRequiresMember\RequiresProperty.',
            ],
        ]);
    }

    public function testReportsWrongReturnTypeOnConcreteClass(): void
    {
        $this->expect(__DIR__ . '/../Cases/ClassRequiresMember/WrongReturnType.php', [
            ['Method Tests\Cases\ClassRequiresMember\WrongReturnType->run requires types string', 7],
            ['Method Tests\Cases\ClassRequiresMember\WrongReturnType->run missing string',        7],
            ['Method Tests\Cases\ClassRequiresMember\WrongReturnType->run has int types.',        7],
        ]);
    }

    public function testReportsWiderReturnTypeAsIgnorableWarning(): void
    {
        $this->expect(__DIR__ . '/../Cases/ClassRequiresMember/WiderReturnType.php', [
            ['Method Tests\Cases\ClassRequiresMember\WiderReturnType->run requires types string', 7],
            ['Method Tests\Cases\ClassRequiresMember\WiderReturnType->run unexpected types int',  7],
            ['Method Tests\Cases\ClassRequiresMember\WiderReturnType->run has string|int types.', 7],
        ]);
    }

    public function testReportsWrongMethodVisibility(): void
    {
        $this->expect(__DIR__ . '/../Cases/ClassRequiresMember/WrongMethodVisibility.php', [
            ['Method Tests\Cases\ClassRequiresMember\WrongMethodVisibility->run protected modifiers.',  7],
            ['Method Tests\Cases\ClassRequiresMember\WrongMethodVisibility->run missing protected',     7],
            ['Method Tests\Cases\ClassRequiresMember\WrongMethodVisibility->run has public modifiers.', 7],
        ]);
    }

    public function testReportsMissingConstFromParentClass(): void
    {
        $this->expect(__DIR__ . '/../Cases/ClassRequiresMember/MissingInheritedConst.php', [
            [
                'Missing Constant Tests\Cases\ClassRequiresMember\MissingInheritedConst::INHERITED_CONST.',
                7,
                'Constant required by Class Tests\Cases\ClassRequiresMember\ParentRequiresConst.',
            ],
        ]);
    }

    public function testReportsMissingPropertyFromTrait(): void
    {
        $this->expect(__DIR__ . '/../Cases/ClassRequiresMember/MissingTraitProperty.php', [
            [
                'Missing Property Tests\Cases\ClassRequiresMember\MissingTraitProperty->id.',
                7,
                'Property required by Trait Tests\Cases\ClassRequiresMember\RequiresIdTrait.',
            ],
        ]);
    }

    public function testPassesWhenConcreteClassSatisfiesContracts(): void
    {
        $this->expect(__DIR__ . '/../Cases/ClassRequiresMember/Satisfied.php', []);
    }

    protected function getRule(): Rule
    {
        return new ClassRequiresMemberRule($this->createReflectionProvider());
    }
}
