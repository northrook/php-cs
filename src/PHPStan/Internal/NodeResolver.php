<?php

declare(strict_types=1);

namespace Northrook\PHPStan\Internal;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\ShouldNotHappenException;

/**
 * @internal
 */
trait NodeResolver
{
    /**
     * @return class-string
     * @throws ShouldNotHappenException
     */
    final protected function resolveName(
        Node $from,
    ): string {
        $nodeName = $from->namespacedName ?? $from->name ?? null;

        if ($nodeName === null) {
            throw new ShouldNotHappenException(
                message: 'The ' . $from::class . ' does somehow not have a ' . Name::class . ' or ' . Node\Identifier::class . '.',
            );
        }

        $className = $nodeName->toString();

        if ($className === '') {
            throw new ShouldNotHappenException(message: 'A ' . $from::class . ' name resolved to empty.');
        }

        return $className;
    }
}
