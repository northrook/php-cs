<?php

namespace PHPSTORM_META {
    use PHPDocTagProvider;

    override(PHPDocTagProvider::getSupportedTags(), map([
        '@const'    => '@const',
        '@abstract' => '@abstract',
    ]));
}
