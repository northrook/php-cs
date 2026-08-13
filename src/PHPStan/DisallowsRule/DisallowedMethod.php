<?php

declare(strict_types=1);

namespace Northrook\PHPStan\DisallowsRule;

/**
 * @internal
 */
final readonly class DisallowedMethod
{
    /**
     * @param non-empty-string  $name
     * @param non-empty-string  $sourceLabel
     */
    public function __construct(
        public string $name,
        public bool   $isStatic,
        public string $sourceLabel,
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
