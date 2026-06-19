<?php

namespace PHPSTORM_META {
    use PHPDocTagProvider;

    override(PHPDocTagProvider::getSupportedTags(), map([
        '@requires-const'    => '@requires-const',
        '@requires-property' => '@requires-property',
        '@requires-method'   => '@requires-method',
        '@abstract'          => '@abstract',
    ]));
}
