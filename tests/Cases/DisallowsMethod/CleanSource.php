<?php

declare(strict_types=1);

namespace Tests\Cases\DisallowsMethod;

/**
 * @disallows __clone(), __toString(), static get()
 */
class CleanSource {}
