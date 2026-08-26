# CSV

CSV files are read using `CsvParser`, `CsvReader`, and a header strategy.

## First row as header

Given:

```csv
name,language,since
PHP,PHP,1995
Javascript,Javascript,1995
Go,Go,2009
```

```php
use DMT\FileStream\Factory\CsvParserFactory;
use DMT\FileStream\Reader\Csv\Header\FirstRowHeader;
use DMT\FileStream\Reader\CsvReader;

$parser = new CsvParserFactory()->fromFile('languages.csv');

$reader = new CsvReader(
    $parser,
    new FirstRowHeader($parser),
);

foreach ($reader->getResults() as $language) {
    echo $language->name;
}
```

The first physical row is used as the header and is not returned as a result.

## Preset header

For CSV files without meaningful column names:

```php
use DMT\FileStream\Reader\Csv\Header\PresetHeader;

$header = new PresetHeader(
    $parser,
    ['name', 'language', 'since'],
);
```

Skip the first physical row when necessary:

```php
$header = new PresetHeader(
    $parser,
    ['name', 'language', 'since'],
    skipFirstRow: true,
);
```

## Numbered columns

Generate names such as `column_0`, `column_1`, and `column_2`:

```php
use DMT\FileStream\Reader\Csv\Header\NumberedColumnsHeader;

$header = new NumberedColumnsHeader($parser);
```

To inspect the first row for the number of columns but exclude it from the results:

```php
$header = new NumberedColumnsHeader(
    $parser,
    skipFirstRow: true,
);
```

## Parser configuration

```php
$factory = new CsvParserFactory([
    'delimiter' => ';',
    'enclosure' => '"',
    'escape' => '\\',
]);

$parser = $factory->fromFile('data.csv');
```

CSV parsers can be created from a file, stream, or string:

```php
$factory->fromFile('data.csv');
$factory->fromStream($stream);
$factory->fromString($csv);
```

## Duplicate columns

Duplicate column names are combined into arrays.

Given:

```csv
id,value,value
1,foo,bar
```

the result is equivalent to:

```php
[
    'id' => '1',
    'value' => ['foo', 'bar'],
]
```

## Repeated headers

Rows that exactly equal the configured header are treated as repeated header rows and skipped.

This is useful for concatenated or exported CSV files containing headers more than once.
