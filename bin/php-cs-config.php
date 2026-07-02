#!/usr/bin/env php
<?php

declare(strict_types = 1);

//region Functions

function format(
    string $s,
    $stream = STDOUT,
): string {
    static $fg = [
        'teal'   => '36',
        'blue'   => '34',
        'yellow' => '33',
    ];

    static $mods = [
        'bold' => '1',
    ];

    if (! \stream_isatty($stream)) {
        return \preg_replace('#</?[^>]+>#', '', $s) ?? $s;
    }

    return \preg_replace_callback(
        '#<([a-z]+)((?: +[a-z]+)*?)>(.*?)</\1>#si',
        static function(array $m) use ($fg, $mods): string {
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

//endregion Functions

//region Bootstrap

$force = \in_array('--force', $argv, true);

$projectRoot = \getcwd();

if ($projectRoot === false || ! \is_file($projectRoot . '/composer.json')) {
    \fwrite(STDERR, format("Run this from a project root containing <teal>composer.json</teal>.\n", STDERR));

    exit(1);
}

$packageRoot         = \dirname(__DIR__);
$packageComposerPath = $packageRoot . '/composer.json';

if (! \is_file($packageComposerPath)) {
    \fwrite(STDERR, format("Package <teal>composer.json</teal> not found at {$packageComposerPath}.\n", STDERR));

    exit(1);
}

/** @var array<string, mixed> $packageComposer */
$packageComposer = \json_decode((string) \file_get_contents($packageComposerPath), true, 512, JSON_THROW_ON_ERROR);

$phpstanVersion = $packageComposer['require']['phpstan/phpstan'] ?? null;
$phpstanScript  = $packageComposer['scripts']['phpstan'] ?? null;

if (! \is_string($phpstanVersion)) {
    \fwrite(STDERR, format('Package composer.json is missing <teal>require.phpstan/phpstan</teal>.', STDERR) . "\n");

    exit(1);
}

if (! \is_string($phpstanScript)) {
    \fwrite(STDERR, format('Package composer.json is missing <teal>scripts.phpstan</teal>.', STDERR) . "\n");

    exit(1);
}

$consumerComposerPath = $projectRoot . '/composer.json';

/** @var array<string, mixed> $consumerComposer */
$consumerComposer = \json_decode((string) \file_get_contents($consumerComposerPath), true, 512, JSON_THROW_ON_ERROR);

//endregion Bootstrap

//region Composer setup

$composerChanged = false;

/**
 * @param array<string, mixed> $section
 */
$mergeString = static function(
    array &$section,
    string $key,
    string $value,
    string $label,
) use (&$composerChanged, $force): void {
    $current = $section[$key] ?? null;

    if ($current === null) {
        $section[$key]   = $value;
        $composerChanged = true;

        return;
    }

    if ($current === $value) {
        return;
    }

    if ($force) {
        $section[$key]   = $value;
        $composerChanged = true;

        return;
    }

    \fwrite(STDERR, format(
        "<teal>composer.json</teal> already sets <blue>{$label}</blue>. Pass <yellow bold>--force</yellow> to update it.\n",
        STDERR,
    ));
};

if (! isset($consumerComposer['require-dev']) || ! \is_array($consumerComposer['require-dev'])) {
    $consumerComposer['require-dev'] = [];
}

$mergeString($consumerComposer['require-dev'], 'phpstan/phpstan', $phpstanVersion, 'require-dev.phpstan/phpstan');

if (! isset($consumerComposer['scripts']) || ! \is_array($consumerComposer['scripts'])) {
    $consumerComposer['scripts'] = [];
}

$mergeString($consumerComposer['scripts'], 'phpstan', $phpstanScript, 'scripts.phpstan');

if ($composerChanged) {
    $json = \json_encode($consumerComposer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $json = \preg_replace(['/\t/m', '/^    /m'], ['    ', '  '], $json);

    if ($json === null || ! \is_string($json)) {
        \fwrite(STDERR, format('Failed to encode <teal>composer.json</teal>.', STDERR) . "\n");

        exit(1);
    }

    if (\file_put_contents($consumerComposerPath, $json . "\n") === false) {
        \fwrite(STDERR, format('Failed to write <teal>composer.json</teal>.', STDERR) . "\n");

        exit(1);
    }
}

//endregion Composer setup

//region Copy config files

$copyConfigFile = static function(
    string $filename,
) use ($packageRoot, $projectRoot, $force): void {
    $source = $packageRoot . '/' . $filename;
    $target = $projectRoot . '/' . $filename;

    if (! \is_file($source)) {
        \fwrite(STDERR, format("Package <teal>{$filename}</teal> not found at {$source}.", STDERR) . "\n");

        exit(1);
    }

    if (\realpath($source) === \realpath($target)) {
        echo format("<teal>{$filename}</teal> is already at the project root.\n");

        return;
    }

    if (\is_file($target) && ! $force) {
        \fwrite(
            STDERR,
            format("<teal>{$filename}</teal> already exists. Pass <yellow bold>--force</yellow> to overwrite.", STDERR)
                . "\n",
        );

        return;
    }

    if (! \copy($source, $target)) {
        \fwrite(STDERR, format("Failed to copy <teal>{$filename}</teal> to {$target}.", STDERR) . "\n");

        exit(1);
    }

    echo format("Copied <teal>{$filename}</teal> to {$target}\n");
};

$copyConfigFile('dprint.json');

//endregion Copy config files

//region Generate phpstan.neon

$generatePhpstanConfig = static function() use ($projectRoot, $force): void {
    $target = $projectRoot . '/phpstan.neon';

    if (\is_file($target) && ! $force) {
        \fwrite(
            STDERR,
            format("<teal>phpstan.neon</teal> already exists. Pass <yellow bold>--force</yellow> to overwrite.", STDERR)
                . "\n",
        );

        return;
    }

    $sourceDir = match (true) {
        \is_dir($projectRoot . '/src') => 'src',
        \is_dir($projectRoot . '/php') => 'php',
        default => 'src',
    };

    $paths = [$sourceDir];

    if (\is_dir($projectRoot . '/tests')) {
        $paths[] = 'tests';
    }

    $lines = [
        'includes:',
        "\t- vendor/northrook/php-cs/extension.neon",
        'parameters:',
        "\tpaths:",
    ];

    foreach ($paths as $path) {
        $lines[] = "\t\t- {$path}";
    }

    $neon = \implode("\n", $lines) . "\n";

    if (\file_put_contents($target, $neon) === false) {
        \fwrite(STDERR, format("Failed to write <teal>phpstan.neon</teal> to {$target}.", STDERR) . "\n");

        exit(1);
    }

    echo format("Generated <teal>phpstan.neon</teal> at {$target}\n");
    echo format("Rules and level come from <blue>php-cs</blue> via the included <teal>extension.neon</teal>.\n");
};

$generatePhpstanConfig();

//endregion Generate phpstan.neon

//region Summary

if ($composerChanged) {
    echo format("\nUpdated <teal>composer.json</teal> with <blue>php-cs</blue> requirements.\n");
    echo format("Run <teal bold>composer update</teal> to install the new dependencies.\n");
} else {
    echo format("\n<teal>composer.json</teal> already has <blue>php-cs</blue> requirements.\n");
}

//endregion Summary
