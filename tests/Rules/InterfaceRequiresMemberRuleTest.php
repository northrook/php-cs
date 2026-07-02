<?php

declare(strict_types = 1);

namespace Tests\Rules;

use Northrook\PHPStan\InterfaceRequiresMemberRule;
use PHPStan\Rules\Rule;
use Tests\PHPStanRuleTest;

/**
 * @extends PHPStanRuleTest<InterfaceRequiresMemberRule>
 */
final class InterfaceRequiresMemberRuleTest extends PHPStanRuleTest
{
    public function testReportsMissingMembersOnInterface(): void
    {
        $this->expect(__DIR__ . '/../Cases/InterfaceRequiresMember/MissingMembers.php', [
            [
                'Missing Constant Tests\Cases\InterfaceRequiresMember\MissingMembers::BASIC_CONST.',
                12,
                'Constant required by Interface Tests\Cases\InterfaceRequiresMember\MissingMembers.',
            ],
            [
                'Missing Property Tests\Cases\InterfaceRequiresMember\MissingMembers->name.',
                12,
                'Property required by Interface Tests\Cases\InterfaceRequiresMember\MissingMembers.',
            ],
            [
                'Missing Method Tests\Cases\InterfaceRequiresMember\MissingMembers->greet.',
                12,
                'Method required by Interface Tests\Cases\InterfaceRequiresMember\MissingMembers.',
            ],
        ]);
    }

    public function testPassesWhenInterfaceDeclaresRequiredMembers(): void
    {
        $this->expect(__DIR__ . '/../Cases/InterfaceRequiresMember/Satisfied.php', []);
    }

    protected function getRule(): Rule
    {
        return new InterfaceRequiresMemberRule($this->createReflectionProvider());
    }
}
