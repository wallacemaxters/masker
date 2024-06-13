# WallaceMaxters/Masker

![](https://github.com/wallacemaxters/masker/actions/workflows/php.yml/badge.svg)

This library helpers your to format the texts or numbers with simple masks.

Instalation:

For installation, your need to use php in 8.1, 8.2 or 8.3 version.

```bash
composer require wallacemaxters/masker
```

Basic Usage:

```php
use WallaceMaxters\Masker\Masker;

$masker = new Masker();

$masker->mask('31995451199', '(00) 00000-0000'); // '(31) 99545-1199'
// or 
$masker('31995451199', '(00) 00000-0000'); // '(31) 99545-1199'
```

You can format texts with any character.

```php
$masker->mask('ABC12345', '[AAA]_(00000)'); // '[ABC]_(12345)'
```


If need, you can return the unmasked value of `string`.

```php
$masker->unmask('[ABC]_(12345)', '[AAA]_(00000)'); // 'ABC12345'
```

The `dynamic` method allows to pass many masks as parameters. This allows that your string are formatted according with size.

```php
$cpf_or_cnpj = ['000.000.000-00', '00.000.000/0000-00'];

$masker->dynamic('45522248327', $cpf_or_cnpj); // '455.222.483-27'

$masker->dynamic('68544172000160', $cpf_or_cnpj); // '68.544.172/0001-60'
```