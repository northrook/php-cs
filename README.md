# PHP Coding Standards for Northrook projects

Shared formatting and static analysis configuration.

This package provides:

- **[dPrint](https://dprint.dev/)** formatting via a shared `dprint.json`
- **[PHPStan](https://phpstan.org/)** at level `9`, with custom rules for native PHPDoc member contracts (`@method`, `@property`, `@const`), `@abstract`, `@static`, `@singleton`, and sealed trait methods

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
vendor/bin/php-cs-config
composer update
```

The script copies the shared `dprint.json`, generates a project `phpstan.neon`, and updates `composer.json`:

- `require-dev` `phpstan/phpstan`
- `scripts.phpstan` `vendor/bin/phpstan analyse`
- `scripts.php-cs-config` `vendor/bin/php-cs-config`
- `scripts.collision` `vendor/bin/collision-check`

After setup, these run as Composer scripts from the project root:

```bash
composer php-cs-config
composer collision
composer phpstan
```

Pass `--force` to overwrite existing config files or refresh values that were already set.

### PHPStan

The custom rules and the enforced **level `9`** live in the package's canonical `extension.neon`.

The setup script generates a thin project `phpstan.neon` that includes that `extension.neon` and declares the analysed `paths`:

```neon
includes:
	- vendor/northrook/php-cs/extension.neon
parameters:
	paths:
		- src
		- tests
```

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

Checked on **concrete classes**. On **interfaces**, only `@method` and `@const` must be declared natively — `@property*` is an implementor contract enforced on concrete classes.

| Tag         | Example                                |
|-------------|----------------------------------------|
| `@const`    | `@const STATUS_CODE` or `@const string STATUS_CODE` |
| `@property` | `@property string $name`               |
| `@method`   | `@method string run()` or `@method static static register()` |

`@property-read` and `@property-write` are treated like `@property` for implementors.

`@method` can require `static`. Types are checked for `@method`, `@property`, and `@const`.

Visibility is not part of standard `@method` / `@property` syntax and is not validated.

On concrete classes, mismatches are reported with stable identifiers (e.g. `requiresMember.method.TypeMissing`).

Unexpected-but-compatible modifiers/types produce ignorable warnings.

Requirements are collected from the class itself, its parents, interfaces, and traits — including nested traits and traits used by parents.

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

Mark members on abstract classes or traits that every descendant must redeclare — including intermediate abstract classes.

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

Each class in the hierarchy must declare its own versions of these members; inheritance alone is not enough.

### `@static` tag

Mark a class (or trait) as a static utility type: it must have a **non-public** constructor (`private` or `protected`). `final` is not required.

```php
/**
 * @static
 */
class Hash
{
    private function __construct() {}

    public static function checksum(string $value): string { /* ... */ }
}
```

Subclasses must follow the same constructor rule. A `@static` trait imposes the rule on every class that uses it — including via nested traits or parents that use the trait.

Reported with the `staticClass.publicConstructor` identifier.

### `@singleton` tag

Mark a class as a singleton façade. It must implement `\Northrook\Contracts\Interfaces\SingletonInterface`. This is intentionally only an interface check — extending `Northrook\Contracts\Singleton` is the usual way to satisfy the pattern, but is not required by the rule.

```php
/**
 * @singleton
 */
final class Debug extends Singleton
{
    // ...
}
```

Reported with the `singleton.missingInterface` identifier.

### Sealed trait methods

Errors when a class, trait, or enum body redeclares a `final` method sealed by a trait — including traits used by parents and nested traits.

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

Overrides in test directories are allowed by default. Configure path segments via `finalTraitMethod.testDirectories` (defaults to `tests`):

```neon
parameters:
	finalTraitMethod:
		testDirectories:
			- tests
			- fixtures
```

Set `testDirectories` to an empty list to enforce the seal everywhere.

## PhpStorm

The package ships `.phpstorm.meta.php`.

PhpStorm recognizes `@const`, `@abstract`, `@static`, and `@singleton` in docblocks (in addition to the built-in `@method` and `@property` support).

## Validation

In this repository:

```bash
composer check   # phpstan + phpunit + collision
composer phpstan
composer test
composer collision
```

## License

[BSD-3-Clause](LICENSE)
