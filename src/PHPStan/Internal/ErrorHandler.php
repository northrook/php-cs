<?php

declare(strict_types = 1);

namespace Northrook\Dev\PHPStan\Internal;

use PHPStan\Rules\{RuleError, RuleErrorBuilder};
use PHPStan\ShouldNotHappenException;

trait ErrorHandler
{
    /** @var RuleErrorBuilder<RuleError>[] */
    private array $errors = [];

    /**
     * @param string  $message
     * @param string  $identifier
     * @param bool    $ignorable
     *
     * @return RuleErrorBuilder<RuleError>
     * @throws \PHPStan\ShouldNotHappenException
     *
     * @final
     */
    protected function error(string $message, string $identifier, bool $ignorable = false): RuleErrorBuilder
    {
        $error = RuleErrorBuilder::message($message)->identifier($identifier);

        if ($ignorable === false) {
            $error->nonIgnorable();
        }

        $this->errors[] = $error;

        return $error;
    }

    /**
     * @return array<array-key,RuleError>
     * @throws ShouldNotHappenException
     * @final
     */
    final protected function errors(): array
    {
        $built = \array_map(fn(RuleErrorBuilder $error) => $error->build(), $this->errors);

        $this->errors = [];

        return $built;
    }
}
