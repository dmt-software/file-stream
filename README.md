# File Stream

`dmt-software/file-stream` provides a common streaming API for reading and processing CSV, JSON, and XML files without 
loading the complete document into memory.

## Installation

```bash
composer require dmt-software/file-stream
```

## Supported formats

- [CSV](docs/CSV.md)
- [JSON](docs/JSON.md)
- [XML](docs/XML.md)
- [any iterable](docs/iterable.md)

## Usage

A reader exposes a header and a stream of results:

```php
$header = $reader->getHeader();

foreach ($reader->getResults() as $result) {
    // process result
}
```

Use `Processor` to validate, filter, offset, and limit results:

```php
use DMT\FileStream\Processor;

$processor = new Processor($reader);
$processor->validate(fn (object $header) => true);
$processor->filter(fn (object $result) => true);
$processor->limit(offset: 0, limit: 100,);

foreach ($processor->getResults() as $result) {
    // process result
}
```

Filters are applied before offset and limit.

## Streaming behavior

Readers are designed for forward-only processing.

JSON and XML readers move through the document until the configured header and result sections are found. When both are 
configured, the header is expected to occur before the results.

Streams cannot be rewound after processing has advanced.

## Exceptions

Package exceptions implement:

```php
DMT\FileStream\Exception\Exception
```

Main exception types:

- `NotFoundException` — expected data or a configured path was not found.
- `ReaderException` — the source could not be read or an unsupported stream operation was attempted.
- `SerializationException` — source data could not be converted to the requested PHP object.
- `ValidationException` — header validation failed.