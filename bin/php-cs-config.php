#!/usr/bin/env php
<?php

declare(strict_types = 1);

$force = \in_array('--force', $argv, true);

$projectRoot = \getcwd();

if ($projectRoot === false || ! \is_file($projectRoot . '/composer.json')) {
    \fwrite(STDERR, "Run this from a project root containing composer.json.\n");

    exit(1);
}

$packageRoot         = \dirname(__DIR__);
$packageComposerPath = $packageRoot . '/composer.json';

if (! \is_file($packageComposerPath)) {
    \fwrite(STDERR, "Package composer.json not found at {$packageComposerPath}.\n");

    exit(1);
}

/** @var array<string, mixed> $packageComposer */
$packageComposer = \json_decode((string) \file_get_contents($packageComposerPath), true, 512, JSON_THROW_ON_ERROR);

$extensionInstallerVersion = $packageComposer['require-dev']['phpstan/extension-installer'] ?? null;
$extensionInstallerAllowed = $packageComposer['config']['allow-plugins']['phpstan/extension-installer'] ?? null;
$phpstanScript             = $packageComposer['scripts']['phpstan'] ?? null;
$fmtScript                 = $packageComposer['scripts']['fmt'] ?? null;

if (! \is_string($extensionInstallerVersion)) {
    \fwrite(STDERR, "Package composer.json is missing require-dev.phpstan/extension-installer.\n");

    exit(1);
}

if ($extensionInstallerAllowed !== true) {
    \fwrite(STDERR, "Package composer.json is missing config.allow-plugins.phpstan/extension-installer.\n");

    exit(1);
}

if (! \is_string($phpstanScript)) {
    \fwrite(STDERR, "Package composer.json is missing scripts.phpstan.\n");

    exit(1);
}

if (! \is_string($fmtScript)) {
    \fwrite(STDERR, "Package composer.json is missing scripts.fmt.\n");

    exit(1);
}

$consumerComposerPath = $projectRoot . '/composer.json';

/** @var array<string, mixed> $consumerComposer */
$consumerComposer = \json_decode((string) \file_get_contents($consumerComposerPath), true, 512, JSON_THROW_ON_ERROR);

$composerChanged = false;

/**
 * @param array<string, mixed> $section
 */
$mergeString = static function(array &$section, string $key, string $value, string $label) use (
    &$composerChanged,
    $force,
): void {
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

    \fwrite(STDERR, "composer.json already sets {$label}. Pass --force to update it.\n");
};

if (! isset($consumerComposer['require-dev']) || ! \is_array($consumerComposer['require-dev'])) {
    $consumerComposer['require-dev'] = [];
}

$mergeString(
    $consumerComposer['require-dev'],
    'phpstan/extension-installer',
    $extensionInstallerVersion,
    'require-dev.phpstan/extension-installer',
);

if (! isset($consumerComposer['config']) || ! \is_array($consumerComposer['config'])) {
    $consumerComposer['config'] = [];
}

if (
    ! isset($consumerComposer['config']['allow-plugins']) || ! \is_array($consumerComposer['config']['allow-plugins'])
) {
    $consumerComposer['config']['allow-plugins'] = [];
}

$currentAllowed = $consumerComposer['config']['allow-plugins']['phpstan/extension-installer'] ?? null;

if ($currentAllowed === null) {
    $consumerComposer['config']['allow-plugins']['phpstan/extension-installer'] = $extensionInstallerAllowed;
    $composerChanged                                                            = true;
} elseif ($currentAllowed !== $extensionInstallerAllowed) {
    if ($force) {
        $consumerComposer['config']['allow-plugins']['phpstan/extension-installer'] = $extensionInstallerAllowed;
        $composerChanged                                                            = true;
    } else {
        \fwrite(
            STDERR,
            'composer.json already sets config.allow-plugins.phpstan/extension-installer. Pass --force to update it.'
            . "\n",
        );
    }
}

if (! isset($consumerComposer['scripts']) || ! \is_array($consumerComposer['scripts'])) {
    $consumerComposer['scripts'] = [];
}

$mergeString($consumerComposer['scripts'], 'phpstan', $phpstanScript, 'scripts.phpstan');
$mergeString($consumerComposer['scripts'], 'fmt', $fmtScript, 'scripts.fmt');

if ($composerChanged) {
    $json = \json_encode($consumerComposer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $json = \preg_replace(['/\t/gm', '/^    /m'], ['    ', '  '], $json);

    if ($json === null || ! \is_string($json)) {
        \fwrite(STDERR, "Failed to encode composer.json.\n");

        exit(1);
    }

    if (\file_put_contents($consumerComposerPath, $json . "\n") === false) {
        \fwrite(STDERR, "Failed to write composer.json.\n");

        exit(1);
    }

    echo "Updated composer.json with php-cs requirements.\n";
} else {
    echo "composer.json already has php-cs requirements.\n";
}

$source = $packageRoot . '/dprint.json';
$target = $projectRoot . '/dprint.json';

if (! \is_file($source)) {
    \fwrite(STDERR, "Package dprint.json not found at {$source}.\n");

    exit(1);
}

if (\realpath($source) === \realpath($target)) {
    echo "dprint.json is already at the project root.\n";

    exit(0);
}

if (\is_file($target) && ! $force) {
    \fwrite(STDERR, "dprint.json already exists. Pass --force to overwrite.\n");

    exit(1);
}

if (! \copy($source, $target)) {
    \fwrite(STDERR, "Failed to copy dprint.json to {$target}.\n");

    exit(1);
}

echo "Copied dprint.json to {$target}\n";
