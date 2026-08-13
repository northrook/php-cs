<?php

declare(strict_types=1);

namespace Northrook\PHPStan\Internal;

use PHPStan\Rules\{RuleError, RuleErrorBuilder};
use PHPStan\ShouldNotHappenException;

/**
 * @internal
 */
trait ErrorHandler
{
    /** @var RuleErrorBuilder<RuleError>[] */
    private array $errors = [];

    /**
     * @return RuleErrorBuilder<RuleError>
     * @throws ShouldNotHappenException
     */
    final protected function error(
        string $message,
        string $identifier,
        bool   $ignorable = false,
    ): RuleErrorBuilder {
        $error = RuleErrorBuilder::message($message)->identifier($identifier);

        if ($ignorable === false) {
            $error->nonIgnorable();
        }

        $this->errors[] = $error;

        return $error;
    }

    /**
     * @return array<array-key, RuleError>
     * @throws ShouldNotHappenException
     */
    final protected function errors(): array
    {
        $built = \array_map(fn(RuleErrorBuilder $error) => $error->build(), $this->errors);

        $this->errors = [];

        return $built;
    }
}
