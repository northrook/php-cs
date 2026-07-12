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

/**
 * Recursively collect PSR-4 class-file FQCN suffixes under a source dir.
 *
 * Only files whose name starts with an uppercase letter and ends in `.php`
 * are considered; lowercase- and dot-prefixed files are reserved for
 * functions and non-autoloaded internals.
 *
 * @return list<string> FQCN suffixes, e.g. `Foo`, `Bar\Baz`
 */
function collectSuffixes(
    string $dir,
    string $prefix = '',
): array {
    $entries = \scandir($dir);

    if ($entries === false) {
        return [];
    }

    $suffixes = [];

    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..' || $entry[0] === '.') {
            continue;
        }

        $path = $dir . '/' . $entry;

        if (\is_dir($path)) {
            $suffixes = [...$suffixes, ...collectSuffixes($path, $prefix . $entry . '\\')];

            continue;
        }

        if (! \str_ends_with($entry, '.php') || ! \ctype_upper($entry[0])) {
            continue;
        }

        $suffixes[] = $prefix . \substr($entry, 0, -4);
    }

    return $suffixes;
}

/**
 * Read and decode a composer.json, or null on failure (warning to STDERR).
 *
 * @return array<string, mixed>|null
 */
function readComposer(
    string $path,
): ?array {
    $contents = \file_get_contents($path);

    if ($contents === false) {
        \fwrite(STDERR, format("Failed to read <teal>{$path}</teal>.\n", STDERR));

        return null;
    }

    try {
        /** @var array<string, mixed> $data */
        $data = \json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    } catch (\JsonException $e) {
        \fwrite(STDERR, format("Invalid JSON in <teal>{$path}</teal>: {$e->getMessage()}\n", STDERR));

        return null;
    }

    return $data;
}

/**
 * Resolve the source dirs a composer.json maps to the flat `Northrook\` prefix.
 *
 * Returns `[]` when the package does not declare the prefix (cannot collide).
 *
 * @param array<string, mixed> $composer
 *
 * @return list<string>
 */
function northrookSourceDirs(
    array $composer,
): array {
    $psr4 = $composer['autoload']['psr-4'] ?? null;

    if (! \is_array($psr4) || ! \array_key_exists('Northrook\\', $psr4)) {
        return [];
    }

    $dirs = $psr4['Northrook\\'];
    $dirs = \is_array($dirs) ? $dirs : [$dirs];
    $dirs = \array_values(\array_filter($dirs, '\is_string'));

    return $dirs === [] ? ['src'] : $dirs;
}

//endregion Functions

//region Bootstrap

$projectRoot = \getcwd();

if ($projectRoot === false) {
    \fwrite(STDERR, format("Unable to resolve the current working directory.\n", STDERR));

    exit(1);
}

$vendorDir = $projectRoot . '/vendor/northrook';

if (! \is_dir($vendorDir)) {
    echo format("No <teal>vendor/northrook/</teal> directory found; skipping collision check.\n");

    exit(0);
}

$composerPaths = \glob($vendorDir . '/*/composer.json');

if ($composerPaths === false || $composerPaths === []) {
    echo format("No <teal>northrook/*</teal> packages installed; skipping collision check.\n");

    exit(0);
}

//endregion Bootstrap

//region Collect sources

/** @var list<array{label: string, dir: string, composer: array<string, mixed>}> $sources */
$sources = [];

$rootComposerPath = $projectRoot . '/composer.json';

if (\is_file($rootComposerPath)) {
    $rootComposer = readComposer($rootComposerPath);

    if ($rootComposer !== null) {
        $rootName = \is_string($rootComposer['name'] ?? null) ? $rootComposer['name'] : \basename($projectRoot);

        $sources[] = ['label' => $rootName . ' (project root)', 'dir' => $projectRoot, 'composer' => $rootComposer];
    }
}

foreach ($composerPaths as $composerPath) {
    $composer = readComposer($composerPath);

    if ($composer === null) {
        continue;
    }

    $packageDir = \dirname($composerPath);

    $sources[] = ['label' => 'northrook/' . \basename($packageDir), 'dir' => $packageDir, 'composer' => $composer];
}

//endregion Collect sources

//region Scan sources

/** @var array<string, array<string, true>> $map suffix => set of source labels */
$map = [];

foreach ($sources as $source) {
    foreach (northrookSourceDirs($source['composer']) as $sourceDir) {
        $absoluteDir = $source['dir'] . '/' . \trim($sourceDir, '/');

        if (! \is_dir($absoluteDir)) {
            continue;
        }

        foreach (collectSuffixes($absoluteDir) as $suffix) {
            $map[$suffix][$source['label']] = true;
        }
    }
}

//endregion Scan sources

//region Report

$collisions = \array_filter($map, static fn(array $owners): bool => \count($owners) > 1);

if ($collisions === []) {
    echo format("No <green>Northrook\\</green> collisions found.\n");

    exit(0);
}

\ksort($collisions);

\fwrite(STDERR, format(
    '<red bold>Found ' . \count($collisions) . " Northrook\\ collision(s):</red>\n",
    STDERR,
));

foreach ($collisions as $suffix => $owners) {
    $packages = \implode(', ', \array_keys($owners));

    \fwrite(STDERR, format(
        "  <yellow bold>Northrook\\{$suffix}</yellow> defined by <blue>{$packages}</blue>\n",
        STDERR,
    ));
}

exit(1);

//endregion Report
