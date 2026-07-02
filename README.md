# PHP Coding Standards for Northrook projects

Shared formatting and static analysis configuration.

This package provides:

- **[dPrint](https://dprint.dev/)** formatting via a shared `dprint.json`
- **[PHPStan](https://phpstan.org/)** at level `9`, with custom rules for native PHPDoc member contracts (`@method`, `@property`, `@const`) and `@abstract`

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

The script copies the shared `dprint.json`, generates a project `phpstan.neon`, and updates `composer.json`:

- `require-dev` `phpstan/phpstan`
- `require-dev` `phpstan/extension-installer`
- `config.allow-plugins` `phpstan/extension-installer`
- `scripts.phpstan` `vendor/bin/phpstan analyse`

Pass `--force` to overwrite existing config files or refresh values that were already set.

### PHPStan

The custom rules and the enforced **level `9`** live in the package's canonical `extension.neon`. They are applied automatically via [`phpstan/extension-installer`](https://github.com/phpstan/extension-installer), which also auto-registers any other PHPStan extensions you install.

The setup script generates a thin project `phpstan.neon` that only declares the analysed `paths`:

- the source directory (`src`, falling back to `php`)
- `tests`, when present

Add any project-specific overrides (paths, `excludePaths`, `ignoreErrors`, a different `level`) to that generated `phpstan.neon`.

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

### Native PHPDoc member contracts

Declare members that implementing or extending types must provide, using standard PHPDoc tags.

Checked on **concrete classes**, and on **interfaces themselves**.

| Tag         | Example                                |
|-------------|----------------------------------------|
| `@const`    | `@const STATUS_CODE` or `@const string STATUS_CODE` |
| `@property` | `@property string $name`               |
| `@method`   | `@method string run()` or `@method static static register()` |

`@property-read` and `@property-write` are treated like `@property`.

Tags can specify modifiers (e.g. `static` on `@method`) and types.

Visibility is not part of standard `@method` / `@property` syntax and is not validated.

On concrete classes, mismatches are reported with stable identifiers (e.g. `requiresMember.method.TypeMissing`).

Unexpected-but-compatible modifiers/types produce ignorable warnings.

Requirements are collected from the class's parents, interfaces, and traits.

```php
/**
 * @method static static register()
 */
abstract class ContractSingleton
{
    final protected static function getInstance(): static
    {
        return self::$instance ??= self::register();
    }
}
```

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

### Sealed trait methods

Errors when a class, trait, or enum body redeclares a `final` method inherited from a directly-used trait.

PHP silently lets the using type override a trait's `final` method, defeating the intended seal (PHP only fatals when a *subclass* overrides an inherited final trait method).

```php
trait Sealed
{
    final public function run(): string
    {
        return 'sealed';
    }
}

final class Broken
{
    use Sealed;

    // finalTraitMethod.overridden
    public function run(): string
    {
        return 'overridden';
    }
}
```

Reported with the `finalTraitMethod.overridden` identifier.

## PhpStorm

The package ships `.phpstorm.meta.php`.

PhpStorm recognizes `@const` and `@abstract` in docblocks (in addition to the built-in `@method` and `@property` support).

## Validation

In this repository:

```bash
composer check   # phpstan + phpunit
composer phpstan
composer test
```

## License

[BSD-3-Clause](LICENSE)
