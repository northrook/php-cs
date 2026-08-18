<?php

declare(strict_types=1);

namespace Northrook\PHPStan\Initializer;

use Northrook\PHPStan\Internal\ClassLabel;
use PHPStan\Reflection\ClassReflection;

/**
 * @internal
 */
final readonly class TaggedMethod
{
    /**
     * @param non-empty-string  $name
     */
    public function __construct(
        public string          $name,
        public ClassReflection $declaringClass,
        public bool            $isStatic,
        public bool            $isConstructor,
    ) {}

    /**
     * @return non-empty-string
     */
    public function sourceLabel(): string
    {
        return ClassLabel::of($this->declaringClass) . '::' . $this->name . '()';
    }
}
