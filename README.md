# PHP Coding Standards for Northrook projects

Using [PHP-CS-Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer).

This coding standard deviates from the **PSR**, focusing on comfort and ergonomics for [me](https://github.com/martinlikescoffee).

> [!IMPORTANT]
> This package is still in development, and intended for internal use.

## Installation

This package is not (yet) available on packagist, but you can install manually by editing your `coposer.json` file:

```json
{
  "require-dev": {
    "northrook/php-cs": "dev-main"
  },
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/northrook/php-cs"
    }
  ]
}
``` 

Then copy the included `.php-cs-fixer.dist.php` file from
`/vendor/northrook/php-cs/.php-cs-fixer.dist.php`to the project root.

## Usage
Run the fixer mnually in the console:
```bash
./vendor/bin/php-cs-fixer fix
```
Your IDE might also have an integration, like [PhpStorm](https://www.jetbrains.com/help/phpstorm/using-php-cs-fixer.html).

## License
[MIT](https://github.com/northrook/html-element/blob/main/LICENSE)