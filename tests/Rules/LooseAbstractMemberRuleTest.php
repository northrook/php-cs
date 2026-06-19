<?php

declare(strict_types = 1);

namespace Tests\Rules;

use Northrook\Dev\PHPStan\LooseAbstractMemberRule;
use PHPStan\Rules\Rule;
use Tests\PHPStanRuleTest;

/**
 * @extends PHPStanRuleTest<LooseAbstractMemberRule>
 */
final class LooseAbstractMemberRuleTest extends PHPStanRuleTest
{
    public function testReportsMissingAbstractMembersOnConcreteClass(): void
    {
        $this->expect(__DIR__ . '/../Cases/LooseAbstractMember/MissingMembers.php', [
            [
                'Missing Constant Tests\Cases\LooseAbstractMember\MissingMembers::LABEL.',
                7,
                'abstract class Tests\Cases\LooseAbstractMember\AbstractBase',
            ],
            [
                'Missing Property Tests\Cases\LooseAbstractMember\MissingMembers->name.',
                7,
                'abstract class Tests\Cases\LooseAbstractMember\AbstractBase',
            ],
            [
                'Missing Method Tests\Cases\LooseAbstractMember\MissingMembers->label.',
                7,
                'abstract class Tests\Cases\LooseAbstractMember\AbstractBase',
            ],
        ]);
    }

    public function testPassesWhenConcreteClassDeclaresAbstractMembers(): void
    {
        $this->expect(__DIR__ . '/../Cases/LooseAbstractMember/Satisfied.php', []);
    }

    public function testReportsMissingAbstractMembersOnAbstractIntermediateClass(): void
    {
        $this->expect(__DIR__ . '/../Cases/LooseAbstractMember/AbstractIntermediate.php', [
            [
                'Missing Constant Tests\Cases\LooseAbstractMember\AbstractIntermediate::LABEL.',
                7,
                'abstract class Tests\Cases\LooseAbstractMember\AbstractBase',
            ],
            [
                'Missing Property Tests\Cases\LooseAbstractMember\AbstractIntermediate->name.',
                7,
                'abstract class Tests\Cases\LooseAbstractMember\AbstractBase',
            ],
            [
                'Missing Method Tests\Cases\LooseAbstractMember\AbstractIntermediate->label.',
                7,
                'abstract class Tests\Cases\LooseAbstractMember\AbstractBase',
            ],
        ]);
    }

    protected function getRule(): Rule
    {
        return new LooseAbstractMemberRule($this->createReflectionProvider());
    }
}
