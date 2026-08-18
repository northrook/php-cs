<?php

declare(strict_types=1);

namespace Tests\Rules;

use Northrook\PHPStan\DisallowsMethodRule;
use PHPStan\Rules\Rule;
use Tests\Support\PHPStanRuleTest;

/**
 * @extends \Tests\Support\PHPStanRuleTest<DisallowsMethodRule>
 */
final class DisallowsMethodRuleTest extends PHPStanRuleTest
{
    public function testPassesCleanSource(): void
    {
        $this->expect(__DIR__ . '/../Cases/DisallowsMethod/CleanSource.php', []);
    }

    public function testReportsSourceDeclaringDisallowedMethod(): void
    {
        $this->expect(__DIR__ . '/../Cases/DisallowsMethod/SourceDeclaresToString.php', [
            [
                'Method Tests\Cases\DisallowsMethod\SourceDeclaresToString::__toString() is disallowed.',
                10,
                'Disallowed by class Tests\Cases\DisallowsMethod\SourceDeclaresToString.',
            ],
        ]);
    }

    public function testPassesCleanSubclass(): void
    {
        $this->expect(__DIR__ . '/../Cases/DisallowsMethod/CleanSubclass.php', []);
    }

    public function testReportsSubclassDeclaringDisallowedMethod(): void
    {
        $this->expect(__DIR__ . '/../Cases/DisallowsMethod/SubclassDeclaresToString.php', [
            [
                'Method Tests\Cases\DisallowsMethod\SubclassDeclaresToString::__toString() is disallowed.',
                7,
                'Disallowed by class Tests\Cases\DisallowsMethod\DisallowToString.',
            ],
        ]);
    }

    public function testReportsImplementorDeclaringDisallowedMethod(): void
    {
        $this->expect(__DIR__ . '/../Cases/DisallowsMethod/ImplementorDeclaresToString.php', [
            [
                'Method Tests\Cases\DisallowsMethod\ImplementorDeclaresToString::__toString() is disallowed.',
                7,
                'Disallowed by interface Tests\Cases\DisallowsMethod\DisallowsToStringInterface.',
            ],
        ]);
    }

    public function testPassesTraitUserWithoutMethod(): void
    {
        $this->expect(__DIR__ . '/../Cases/DisallowsMethod/UsesTraitClean.php', []);
    }

    public function testReportsTraitUserDeclaringDisallowedMethod(): void
    {
        $this->expect(__DIR__ . '/../Cases/DisallowsMethod/UsesTraitDeclaresToString.php', [
            [
                'Method Tests\Cases\DisallowsMethod\UsesTraitDeclaresToString::__toString() is disallowed.',
                7,
                'Disallowed by trait Tests\Cases\DisallowsMethod\DisallowsToStringTrait.',
            ],
        ]);
    }

    public function testReportsMethodInheritedWhileDisallowed(): void
    {
        $this->expect(__DIR__ . '/../Cases/DisallowsMethod/InheritsWhileDisallowed.php', [
            [
                'Method Tests\Cases\DisallowsMethod\InheritsWhileDisallowed::__toString() is disallowed.',
                10,
                'Disallowed by trait Tests\Cases\DisallowsMethod\DisallowsToStringTrait.',
            ],
        ]);
    }

    public function testReportsStaticGetWhenDisallowed(): void
    {
        $this->expect(__DIR__ . '/../Cases/DisallowsMethod/DeclaresStaticGet.php', [
            [
                'Method Tests\Cases\DisallowsMethod\DeclaresStaticGet::static get() is disallowed.',
                7,
                'Disallowed by class Tests\Cases\DisallowsMethod\DisallowsStaticGet.',
            ],
        ]);
    }

    public function testPassesInstanceGetWhenOnlyStaticDisallowed(): void
    {
        $this->expect(__DIR__ . '/../Cases/DisallowsMethod/DeclaresInstanceGet.php', []);
    }

    public function testReportsNestedTraitPropagation(): void
    {
        $this->expect(__DIR__ . '/../Cases/DisallowsMethod/UsesNestedTraitDeclaresToString.php', [
            [
                'Method Tests\Cases\DisallowsMethod\UsesNestedTraitDeclaresToString::__toString() is disallowed.',
                7,
                'Disallowed by trait Tests\Cases\DisallowsMethod\DisallowsToStringTrait.',
            ],
        ]);
    }

    public function testPassesNestedTraitWithoutMethod(): void
    {
        $this->expect(__DIR__ . '/../Cases/DisallowsMethod/UsesNestedTraitClean.php', []);
    }

    public function testIgnoresUnconstrainedClass(): void
    {
        $this->expect(__DIR__ . '/../Cases/DisallowsMethod/Unconstrained.php', []);
    }

    protected function getRule(): Rule
    {
        return new DisallowsMethodRule($this->createReflectionProvider());
    }
}
