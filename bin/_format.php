<?php

declare(strict_types=1);

/**
 * @param resource  $stream
 */
function format(
    string $s,
           $stream = STDOUT,
): string {
    static $fg = [
        'teal'   => '36',
        'blue'   => '34',
        'yellow' => '33',
        'red'    => '31',
        'green'  => '32',
    ];

    static $mods = [
        'bold' => '1',
    ];

    if (! \stream_isatty($stream)) {
        return \preg_replace('#</?[^>]+>#', '', $s) ?? $s;
    }

    return \preg_replace_callback(
        '#<([a-z]+)((?: +[a-z]+)*?)>(.*?)</\1>#si',
        static function(
            array $m,
        ) use ($fg, $mods): string {
            $modNames = $m[2] === '' ? [] : \preg_split('/ +/', \trim($m[2]));

            $parts = [];
            foreach ($modNames as $mod) {
                if (isset($mods[$mod])) {
                    $parts[] = $mods[$mod];
                }
            }
            if (isset($fg[$m[1]])) {
                $parts[] = $fg[$m[1]];
            }
            if ($parts === []) {
                return $m[3];
            }

            $open = "\033[" . \implode(';', $parts) . 'm';

            return $open . $m[3] . "\033[0m";
        },
        $s,
    ) ?? $s;
}
