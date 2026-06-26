<?php

declare(strict_types = 1);

namespace Tests\Cases\InterfaceRequiresMember;

/**
 * @const    BASIC_CONST
 * @property string $name
 * @method   void greet()
 */
interface MissingMembers {}
