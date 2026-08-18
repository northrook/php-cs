<?php

declare(strict_types=1);

namespace Tests\Extensions;

use Northrook\PHPStan\TaggedAdditionalConstructorsExtension;
use PHPStan\Testing\PHPStanTestCase;

final class TaggedAdditionalConstructorsExtensionTest extends PHPStanTestCase
{
    public function testReturnsTaggedTraitMethodForUsingClass(): void
    {
        $extension  = new TaggedAdditionalConstructorsExtension;
        $reflection = $this->createReflectionProvider()->getClass('Tests\Cases\Initializer\Satisfied');

        self::assertSame(['initializeValue'], $extension->getAdditionalConstructors($reflection));
    }

    public function testReturnsEmptyForUntaggedClass(): void
    {
        $extension  = new TaggedAdditionalConstructorsExtension;
        $reflection = $this->createReflectionProvider()->getClass('Tests\Cases\Initializer\Untagged');

        self::assertSame([], $extension->getAdditionalConstructors($reflection));
    }

    public function testSkipsStaticTaggedMethods(): void
    {
        $extension  = new TaggedAdditionalConstructorsExtension;
        $reflection = $this->createReflectionProvider()->getClass('Tests\Cases\Initializer\StaticMethod');

        self::assertSame([], $extension->getAdditionalConstructors($reflection));
    }
}
