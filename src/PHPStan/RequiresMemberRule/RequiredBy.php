<?php

declare(strict_types=1);

namespace Northrook\PHPStan\RequiresMemberRule;

use PHPStan\ShouldNotHappenException;
use Stringable;

/**
 * @internal
 *
 * @used-by \Northrook\PHPStan\RequiresMemberRule\RequiredMember
 */
final readonly class RequiredBy implements Stringable
{
    /**
     * @param 'Class'|'Interface'|'Trait'  $type
     * @param string                       $className
     */
    private function __construct(
        public string $type,
        public string $className,
    ) {}

    public function __toString(): string
    {
        return $this->type . ' ' . $this->className;
    }

    /**
     * @internal
     *
     * @param string  $type
     * @param string  $className
     *
     * @return RequiredBy
     * @throws ShouldNotHappenException
     */
    public static function from(string $type, string $className): RequiredBy
    {
        if (false === ( $type === 'Class' || $type === 'Interface' || $type === 'Trait' )) {
            throw new ShouldNotHappenException(
                message: __CLASS__
                . ' $type must be one of `Class|Interface|Trait`, '
                . \var_export($type, true)
                . ' provided.',
            );
        }

        if (
            \preg_match(
                '/^[A-Za-z_\x80-\xff][A-Za-z0-9_\x80-\xff]*(?:\\\\[A-Za-z_\x80-\xff][A-Za-z0-9_\x80-\xff]*)*$/',
                $className,
            ) !== 1
        ) {
            throw new ShouldNotHappenException(
                message: __CLASS__
                . ' $className must be a valid class name, '
                . \var_export($className, true)
                . ' provided.',
            );
        }

        return new self($type, $className);
    }
}
