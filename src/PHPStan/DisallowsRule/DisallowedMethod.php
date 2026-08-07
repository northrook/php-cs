<?php

declare(strict_types=1);

namespace Northrook\PHPStan\DisallowsRule;

/**
 * @internal
 */
final class DisallowedMethod
{
    /**
     * @param non-empty-string  $name
     * @param non-empty-string  $sourceLabel
     * @param class-string      $sourceClass
     */
    public function __construct(
        public readonly string $name,
        public readonly bool   $isStatic,
        public readonly string $sourceLabel,
        public readonly string $sourceClass,
    ) {}

    /**
     * @return non-empty-string
     */
    public function key(): string
    {
        return ( $this->isStatic ? 'static:' : 'instance:' ) . \strtolower($this->name);
    }

    /**
     * @return non-empty-string
     */
    public function display(): string
    {
        return ( $this->isStatic ? 'static ' : '' ) . $this->name . '()';
    }
}
