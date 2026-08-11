<?php

declare(strict_types=1);

namespace Northrook\PHPStan;

use Northrook\PHPStan\Internal\ErrorHandler;
use PhpParser\Node;
use PhpParser\Node\{Expr\FuncCall, Name};
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\{Rule, RuleError};
use PHPStan\ShouldNotHappenException;

/**
 * Reports calls to configured disallowed functions.
 *
 * Configured names are absolute (`var_export` ⇒ `\var_export`).
 *
 * @implements Rule<FuncCall>
 */
final class DisallowedFunctionCallsRule implements Rule
{
    use ErrorHandler;

    /** @var array<string, null|string> lowercased absolute function name => tip message */
    private readonly array $disallowedFunctions;

    /**
     * @param list<array{function: string, message?: string}>  $disallowedFunctions
     */
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        array                               $disallowedFunctions,
    ) {
        $normalized = [];

        foreach ($disallowedFunctions as $entry) {
            $name = \strtolower(\trim($entry['function'], "\\ \t\n\r\0\x0B()"));

            if ($name === '') {
                continue;
            }

            $normalized[$name] = $entry['message'] ?? null;
        }

        $this->disallowedFunctions = $normalized;
    }

    /**
     * @return array<array-key, RuleError>
     *
     * @throws ShouldNotHappenException
     */
    public function processNode(
        Node  $node,
        Scope $scope,
    ): array {
        if ($this->disallowedFunctions === [] || ! $node->name instanceof Name) {
            return [];
        }

        $absolute = $this->absoluteFunctionName($node->name, $scope);

        if ($absolute === null || ! \array_key_exists(\strtolower($absolute), $this->disallowedFunctions)) {
            return [];
        }

        $function = $absolute . '()';
        $tip      = $this->disallowedFunctions[\strtolower($absolute)] ?? "{$function} is disallowed.";

        $this
            ->error(
                message   : "Call to function {$function} is disallowed.",
                identifier: 'disallowedFunctionCalls.' . $this->identifierSuffix($absolute),
            )
            ->tip($tip);

        return $this->errors();
    }

    /**
     * Absolute function name as Reflection reports it (no leading `\`).
     */
    private function absoluteFunctionName(
        Name  $name,
        Scope $scope,
    ): ?string {
        if ($this->reflectionProvider->hasFunction($name, $scope)) {
            return $this->reflectionProvider->getFunction($name, $scope)->getName();
        }

        // Unknown to reflection: still match absolute / FQ written names against config.
        if ($name->isFullyQualified()) {
            return $name->toString();
        }

        return null;
    }

    private function identifierSuffix(
        string $function,
    ): string {
        $suffix = \str_replace(
            ' ',
            '',
            \ucwords(\str_replace(['\\', '_'], ' ', $function)),
        );

        return $suffix !== '' ? \lcfirst($suffix) : 'call';
    }

    public function getNodeType(): string
    {
        return FuncCall::class;
    }
}
