<?php

declare(strict_types = 1);

namespace Northrook\Dev\PHPStan\Internal;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\ShouldNotHappenException;

/**
 * @internal
 */
trait NodeResolver
{
    /**
     * @param string $string
     *
     * @return string[]
     */
    final protected function explodeDocBlock(string $string): array
    {
        // Normalize newline
        if (\str_contains($string, "\r")) {
            $string = \strtr($string, ["\r\n" => "\n", "\r" => "\n"]);
        }

        return \explode("\n", $string);
    }

    /**
     * @param Node $from
     *
     * @return class-string
     * @throws ShouldNotHappenException
     * @final
     */
    final protected function resolveName(Node $from): string
    {
        $nodeName = $from->namespacedName ?? $from->name ?? null;

        if ($nodeName === null) {
            throw new ShouldNotHappenException(
                message: 'The '
                . $from::class
                . ' does somehow not have a '
                . Name::class
                . ' or '
                . Node\Identifier::class
                . '.',
            );
        }

        $className = $nodeName->toString();

        if ($className === '') {
            throw new ShouldNotHappenException(message: 'A ' . $from::class . ' name resolved to empty.');
        }

        return $className;
    }
}
