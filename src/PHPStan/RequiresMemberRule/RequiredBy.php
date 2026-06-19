<?php

declare(strict_types = 1);

namespace Northrook\Dev\PHPStan\RequiresMemberRule;

use PHPStan\ShouldNotHappenException;
use Stringable;

/**
 * @internal
 *
 * @used-by \Northrook\Dev\PHPStan\RequiresMemberRule\RequiredMember
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

        if (\ctype_alnum(\strtr($className, ['\\' => ''])) === false) {
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
