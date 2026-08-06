<?php

declare(strict_types=1);

namespace Tests\Rules;

use Northrook\PHPStan\InterfaceRequiresMemberRule;
use PHPStan\Rules\Rule;
use Tests\PHPStanRuleTest;

/**
 * @extends PHPStanRuleTest<InterfaceRequiresMemberRule>
 */
final class InterfaceRequiresMemberRuleTest extends PHPStanRuleTest
{
    public function testReportsMissingConstOnInterface(): void
    {
        $this->expect(__DIR__ . '/../Cases/InterfaceRequiresMember/MissingMembers.php', [
            [
                'Missing Constant Tests\Cases\InterfaceRequiresMember\MissingMembers::BASIC_CONST.',
                12,
                'Constant required by Interface Tests\Cases\InterfaceRequiresMember\MissingMembers.',
            ],
        ]);
    }

    public function testPassesWhenInterfaceDeclaresRequiredConst(): void
    {
        $this->expect(__DIR__ . '/../Cases/InterfaceRequiresMember/Satisfied.php', []);
    }

    public function testPassesWhenInterfaceOnlyDocumentsMethod(): void
    {
        $this->expect(__DIR__ . '/../Cases/InterfaceRequiresMember/MethodOnly.php', []);
    }

    protected function getRule(): Rule
    {
        return new InterfaceRequiresMemberRule($this->createReflectionProvider());
    }
}
