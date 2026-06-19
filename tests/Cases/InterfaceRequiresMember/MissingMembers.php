<?php

declare(strict_types = 1);

namespace Tests\Cases\InterfaceRequiresMember;

/**
 * @requires-const    BASIC_CONST
 * @requires-property string $name
 * @requires-method   greet()
 */
interface MissingMembers {}
