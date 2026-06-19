<?php

declare(strict_types = 1);

namespace Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @template TRule of Rule
 * @extends RuleTestCase<TRule>
 */
abstract class PHPStanRuleTest extends RuleTestCase
{
    /**
     * @param list<array{0: string, 1: int, 2?: null|string}>  $errors
     */
    final protected function expect(string $fixture, array $errors): void
    {
        $this->analyse([$fixture], $errors);
    }
}
