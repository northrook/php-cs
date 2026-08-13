<?php

declare(strict_types=1);

namespace Tests\Rules;

use Northrook\PHPStan\DisallowedFunctionCallsRule;
use PHPStan\Rules\Rule;
use Tests\PHPStanRuleTest;

/**
 * @extends PHPStanRuleTest<DisallowedFunctionCallsRule>
 */
final class DisallowedFunctionCallsRuleTest extends PHPStanRuleTest
{
    /** @var list<array{function: string, message?: string, exceptIn?: string|list<string>}> */
    private array $disallowedFunctions = [
        [
            'function' => 'var_export()',
            'message'  => 'Use Serializer/Snapshot/VarExporter; native var_export leaks object props.',
            'exceptIn' => 'Tests\Cases\DisallowedFunctionCalls\Exportable',
        ],
    ];

    public function testReportsDisallowedFunctionCall(): void
    {
        $this->expect(__DIR__ . '/../Cases/DisallowedFunctionCalls/Disallowed.php', [
            [
                'Call to function var_export() is disallowed.',
                12,
                'Use Serializer/Snapshot/VarExporter; native var_export leaks object props.',
            ],
        ]);
    }

    public function testReportsUnqualifiedCallViaGlobalFallback(): void
    {
        $this->expect(__DIR__ . '/../Cases/DisallowedFunctionCalls/GlobalFallback.php', [
            [
                'Call to function var_export() is disallowed.',
                12,
                'Use Serializer/Snapshot/VarExporter; native var_export leaks object props.',
            ],
        ]);
    }

    public function testPassesAllowedDynamicAndMethodCalls(): void
    {
        $this->expect(__DIR__ . '/../Cases/DisallowedFunctionCalls/Satisfied.php', []);
    }

    public function testIgnoresNamespacedFunctionWithSameName(): void
    {
        $this->disallowedFunctions = [
            ['function' => 'disallowed_helper()'],
        ];

        $this->expect(__DIR__ . '/../Cases/DisallowedFunctionCalls/NamespacedShadow.php', []);
    }

    public function testReportsBannedNamespacedFunction(): void
    {
        $this->disallowedFunctions = [
            ['function' => 'Tests\Cases\DisallowedFunctionCalls\disallowed_helper()'],
        ];

        $this->expect(__DIR__ . '/../Cases/DisallowedFunctionCalls/NamespacedBanned.php', [
            [
                'Call to function Tests\Cases\DisallowedFunctionCalls\disallowed_helper() is disallowed.',
                18,
                'Tests\Cases\DisallowedFunctionCalls\disallowed_helper() is disallowed.',
            ],
        ]);
    }

    public function testReportsWithDefaultTipWhenMessageOmitted(): void
    {
        $this->disallowedFunctions = [
            ['function' => 'var_export()'],
        ];

        $this->expect(__DIR__ . '/../Cases/DisallowedFunctionCalls/NoMessage.php', [
            [
                'Call to function var_export() is disallowed.',
                12,
                'var_export() is disallowed.',
            ],
        ]);
    }

    public function testAllowsCallInClassImplementingExceptInInterface(): void
    {
        $this->expect(__DIR__ . '/../Cases/DisallowedFunctionCalls/AllowedInExportable.php', []);
    }

    public function testAllowsCallWhenExceptInIsAList(): void
    {
        $this->disallowedFunctions = [
            [
                'function' => 'var_export()',
                'exceptIn' => [
                    'Tests\Cases\DisallowedFunctionCalls\Missing',
                    '\Tests\Cases\DisallowedFunctionCalls\Exportable',
                ],
            ],
        ];

        $this->expect(__DIR__ . '/../Cases/DisallowedFunctionCalls/AllowedInExportable.php', []);
    }

    public function testReportsWhenClassIsNotInExceptIn(): void
    {
        $this->disallowedFunctions = [
            [
                'function' => 'var_export()',
                'message'  => 'Use Serializer/Snapshot/VarExporter; native var_export leaks object props.',
                'exceptIn' => 'Tests\Cases\DisallowedFunctionCalls\Exportable',
            ],
        ];

        $this->expect(__DIR__ . '/../Cases/DisallowedFunctionCalls/Disallowed.php', [
            [
                'Call to function var_export() is disallowed.',
                12,
                'Use Serializer/Snapshot/VarExporter; native var_export leaks object props.',
            ],
        ]);
    }

    protected function getRule(): Rule
    {
        return new DisallowedFunctionCallsRule(
            $this->createReflectionProvider(),
            $this->disallowedFunctions,
        );
    }
}
