# Fixed column width parser

Parser for fixed column width files.

## Installation

Via [Composer](https://getcomposer.org/) :

```shell
$ composer install zol/fixed-column-width-parser
```

## Usage

First, you have to define your file schema. Then, you can parse a file according to the previously defined schema.

```php
<?php
use Zol\Parser\FixedColumnWidth\Parser;

$schema = [
    'entry' => [ 'name' => 10 ]
];

$parser = new Parser($schema, 'import.dat');
$parser->parse();
```

## Schema reference

```php
[
    // Ignored lines, null if none
    // First line is indexed by 1
    // Optionnal, null by default
    'ignore' => [1, 8 , 9],

    // Header line, null if missing
    // Optionnal, null by default
    'header' => ['field-name' => length, 'field-name' => length],

    // Define entry schema
    // Required
    'entry' => ['field-name' => length, 'field-name' => length],

    // Use header values as entry field names
    // If true, entry field names will be replaced with header values
    // Optionnal, false by default
    'header-as-field-name' => false,

    // Ignore empty line
    // Optionnal, true by default
    'ignore-empty-lines' => true,

    // Multiple files in one
    // If true, you must define separator
    // Optionnal, default false,
    'multiple' => false,

    // Separator, only used if multiple is true
    // Define files separator
    'separator' => [
        'field' => length, // Separator field
        'values' => [ 'value', 'value'], // Field values considered as separator
        'ignore' => true // Ignore separation line
    ]
]
```
## Examples

### Standard use case

```
id  name     phone      street
----------------------------------
123 john     0152458652 5th street

321 isabelle 0352158546 4th street
```

```php
<?php
use Zol\Parser\FixedColumnWidth\Parser;

$schema = [
    'ignore' => [2],
    'header' => [4, 9, 11, 10],
    'entry' => [4, 9, 11, 10],
    'header-as-field-name' => true
];

$parser = new Parser($schema, 'import.dat');
$parser->parse();

// Returns :
[
    'header' => [
        'id', 'name', 'phone', 'street'
    ],
    'entries' => [
        ['id' => '123', 'name' => 'john', 'phone' => '0152458652', 'street' => '5th street'],
        ['id' => '321', 'name' => 'isabelle', 'phone' => '0352158546', 'street' => '4th street']
    ]
]
```

### Multiple files in one

```
id  name     phone      street
----------------------------------
123 john     0152458652 5th street

321 isabelle 0352158546 4th street
id  name     phone_num  street
----------------------------------
123 john     0152458652 1st street
321 isabelle 0352158546 2nd street
```

```php
<?php
use Zol\Parser\FixedColumnWidth\Parser;

$schema = [
    'ignore' => [2],
    'header' => [4, 9, 11, 10],
    'entry' => [4, 9, 11, 10],
    'header-as-field-name' => true,
    'multiple' => true,
    'separator' => [
        'field' => 4,
        'values' => ['id'],
        'ignore' => false
    ]
];

$parser = new Parser($schema, 'import-multiple.dat');
$parser->parse();

// Returns :
[
    [
        'header' => ['id', 'name', 'phone', 'street'],
        'entries' => [
            ['id' => '123', 'name' => 'john', 'phone' => '0152458652', 'street' => '5th street'],
            ['id' => '321', 'name' => 'isabelle', 'phone' => '0352158546', 'street' => '4th street']
        ]
    ],
    [
        'header' => ['id', 'name', 'phone_num', 'street'],
        'entries' => [
            ['id' => '123', 'name' => 'john', 'phone_num' => '0152458652', 'street' => '1st street'],
            ['id' => '321', 'name' => 'isabelle', 'phone_num' => '0352158546', 'street' => '2nd street']
        ]
    ],

]
```

See [tests](tests/units/Parser.php) for more examples.

## Tests

```
$ ./vendor/bin/atoum
```
