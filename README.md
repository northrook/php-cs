# PHP Coding Standards for Northrook projects

Shared formatting and static analysis configuration.

This package provides:

- **[dPrint](https://dprint.dev/)** formatting via a shared `dprint.json`
- **[PHPStan](https://phpstan.org/)** at level `9`, with custom rules for `@requires-*` and `@abstract` member contracts

The conventions here prioritize ergonomics over PSR alignment.


## Requirements

- PHP 8.4+
- [Composer](https://getcomposer.org/)
- [dPrint CLI](https://dprint.dev/install/) (optional, for formatting)
- [PHPStan](https://phpstan.org/) `2.2+`

## Installation

```bash
composer require --dev northrook/php-cs
```

## Quick start

Add the package, then run the setup script from your project root:

```bash
composer require --dev northrook/php-cs
vendor/bin/php-cs-config.php
composer update
```

The script copies shared `dprint.json` and `phpstan.neon` into the project root, and updates `composer.json`:

- `require-dev` `phpstan/phpstan`
- `require-dev` `phpstan/extension-installer`
- `config.allow-plugins` `phpstan/extension-installer`
- `scripts.phpstan` `vendor/bin/phpstan analyse`

Pass `--force` to overwrite existing config files or refresh values that were already set.

### PHPStan

The setup script copies `phpstan.neon` to the project root. Edit it there for project-specific overrides.

The shipped config enforces **level `9`**, and analyses `./src` and `./tests`.

Run PHPStan from the project root:

```bash
composer phpstan
```

### dPrint

Install the [dPrint CLI](https://dprint.dev/install/).

The setup script copies the shared config into the project.

Format PHP files:

```bash
dprint fmt
```

## Custom PHPStan rules

### `@requires-*` tags

Declare members that implementing or extending types must provide.

Checked on **concrete classes**, and on **interfaces themselves**.

| Tag                  | Example                           |
|----------------------|-----------------------------------|
| `@requires-const`    | `@requires-const STATUS_CODE`     |
| `@requires-property` | `@requires-property string $name` |
| `@requires-method`   | `@requires-method run(): string`  |

Tags can specify modifiers and types.

On concrete classes, mismatches are reported with stable identifiers (e.g.`requiresMember.method.TypeMissing`).

Unexpected-but-compatible modifiers/types produce ignorable warnings.

Requirements check the class's parents, interfaces, and traits.

### `@abstract` tag

Mark members on abstract classes or traits that every concrete descendant must redeclare.

```php
abstract class Base
{
    /** @abstract */
    public const string LABEL = 'base';

    /** @abstract */
    protected string $name = 'base';

    /** @abstract */
    public function label(): string
    {
        return self::LABEL;
    }
}
```

A concrete class must declare its own versions of these members, inheritance alone is not enough.

## PhpStorm

The package ships `.phpstorm.meta.php`.

PhpStorm recognizes `@requires-const`, `@requires-property`, `@requires-method`, and `@abstract` in docblocks.

## Validation

In this repository:

```bash
composer check   # phpstan + phpunit
composer phpstan
composer test
```

## License

[BSD-3-Clause](LICENSE)
