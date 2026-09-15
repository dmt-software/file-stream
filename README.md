# File Stream

Streaming readers and writers for CSV, JSON and XML.

## Installation

```bash
composer require dmt-software/file-stream
```

# Reading

Read structured data as objects.

```php
use DMT\FileStream\Config\JsonReaderConfig;
use DMT\FileStream\Reader\JsonReader;
use DMT\FileStream\Stream\JsonReaderStream;
use DMT\FileStream\Structured\Path\DotSeparatedPath;

$reader = new JsonReader(
    new JsonReaderStream(fopen('people.json', 'r')),
    new JsonReaderConfig(
        path: new DotSeparatedPath('.people')
    )
);

foreach ($reader->getResults() as $person) {
    echo $person->firstName;
}
```

Available readers:

```text
CsvReader
JsonReader
XmlReader
```

# Filtering

Reader results can be filtered and paginated with `ReadStatement`.

```php
use DMT\FileStream\ReadStatement;

$results = (new ReadStatement($reader))
    ->where('object.age >= 18')
    ->limit(
        offset: 10,
        limit: 20
    )
    ->execute();
```

Offset and limit are applied after filtering.

# Writing

Serialize objects directly to a stream.

```php
use ArrayObject;
use DMT\FileStream\Config\CsvWriterConfig;
use DMT\FileStream\Record\Mapping\Csv\PredefinedNamedColumnMapper;
use DMT\FileStream\Stream\ResourceWriterStream;
use DMT\FileStream\Writer\CsvWriter;

$writer = new CsvWriter(
    new ResourceWriterStream(fopen('people.csv', 'w')),
    new CsvWriterConfig(
        columnMapper: new PredefinedNamedColumnMapper([
            'firstName',
            'lastName',
            'age',
        ])
    )
);

$writer->write([
    new ArrayObject([
        'firstName' => 'John',
        'lastName' => 'Doe',
        'age' => 42,
    ]),
]);
```

Available writers:

```text
CsvWriter
JsonWriter
XmlWriter
```

Writers flush and close their output stream when writing completes.
