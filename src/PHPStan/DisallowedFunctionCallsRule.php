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
 * Optional `exceptIn` skips the error when the call is in a class that is,
 * extends, or implements that type.
 *
 * @implements Rule<FuncCall>
 */
final class DisallowedFunctionCallsRule implements Rule
{
    use ErrorHandler;

    /**
     * @var array<string, array{message: null|string, exceptIn: list<string>}>
     */
    private readonly array $disallowedFunctions;

    /**
     * @param list<array{function: string, message?: string, exceptIn?: string|list<string>}>  $disallowedFunctions
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

            $normalized[$name] = [
                'message'  => $entry['message'] ?? null,
                'exceptIn' => $this->normalizeExceptIn($entry['exceptIn'] ?? []),
            ];
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
        $rule     = $absolute === null ? null : $this->disallowedFunctions[\strtolower($absolute)] ?? null;

        if ($absolute === null || $rule === null || $this->isExempt($scope, $rule['exceptIn'])) {
            return [];
        }

        $function = $absolute . '()';
        $tip      = $rule['message'] ?? "{$function} is disallowed.";

        $this->error(
            message   : "Call to function {$function} is disallowed.",
            identifier: 'disallowedFunctionCalls.' . $this->identifierSuffix($absolute),
        )->tip($tip);

        return $this->errors();
    }

    /**
     * Absolute function name as Reflection reports it (no leading `\`).
     */
    private function absoluteFunctionName(
        Name  $name,
        Scope $scope,
    ): null|string {
        if ($this->reflectionProvider->hasFunction($name, $scope)) {
            return $this->reflectionProvider->getFunction($name, $scope)->getName();
        }

        // Unknown to reflection: still match absolute / FQ written names against config.
        if ($name->isFullyQualified()) {
            return $name->toString();
        }

        return null;
    }

    /**
     * @param string|list<string>  $exceptIn
     *
     * @return list<string>
     */
    private function normalizeExceptIn(
        string|array $exceptIn,
    ): array {
        if (\is_string($exceptIn)) {
            $exceptIn = [$exceptIn];
        }

        $normalized = [];

        foreach ($exceptIn as $type) {
            $type = \trim($type, "\\ \t\n\r\0\x0B");

            if ($type !== '') {
                $normalized[] = $type;
            }
        }

        return $normalized;
    }

    /**
     * @param list<string>  $exceptIn
     */
    private function isExempt(
        Scope $scope,
        array $exceptIn,
    ): bool {
        $class = $scope->getClassReflection();

        if ($exceptIn === [] || $class === null) {
            return false;
        }

        foreach ($exceptIn as $type) {
            if ($class->is($type)) {
                return true;
            }
        }

        return false;
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
