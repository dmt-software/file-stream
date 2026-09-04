# dmt-software/file-stream

Streaming object readers and writers for CSV, JSON and XML.

The package is built around a small set of composable abstractions:

```text
source
  ↓
ObjectReaderInterface<T>
  ↓
ReadStatement<T>
  ↓
WritePipeline<T, R>
  ↓
ObjectWriterInterface<R>
  ↓
output
```

Readers expose objects lazily. A `ReadStatement` can filter, slice and modify that stream. A `WritePipeline` can optionally transform the objects before passing them to an object writer.

## Installation

```bash
composer require dmt-software/file-stream
```

## Formats

### CSV

The `CsvObjectReader` reads a CSV stream into a series of `ArrayObject`s.

For common use cases, use the configured reader:

```php
$reader = new CsvObjectReader(
    stream: $stream,
    delimiter: ';',
    firstRowDefinesColumns: true,
);
```

Its naming strategy can be overridden when needed:

```php
$reader->setNamingStrategy(
    new NamedPropertyStrategy(propertyNames: ['name', 'tag', 'tag'])
);
```

> NOTE: the first row is returned even when it contains just the column names.


A `CsvObjectWriter` writes a series of `ArrayObject`s to a CSV stream.

```php
$writer = new CsvObjectWriter(
    stream: $stream,
    delimiter: ';',
);
```

Its column strategy can be overridden when needed:

```php
$writer->setColumnStrategy(
    new NamedColumnStrategy(columnNames: ['id','name','email'])
);
```

> NOTE: a property containing a list of associative arrays or objects will be discarded.

### JSON

The `JsonObjectReader` provides a configured JSON reader that yields `stdClass` objects.

```php
$reader = new JsonObjectReader(
    stream: $stream,
    path: '.to.objects'
);
```

The reader above  will return a `stdClass` for each value in the object list `{"to":{"objects":[{...}, {...}, {...}]}}`.

> NOTE: `JSON_OBJECT_AS_ARRAY` will trigger an error because the expected result contains of objects.

A `JsonObjectWriter` writes a series of `stdClass` objects into a JSON stream.

```php
$writer = JsonObjectWriter(
    stream: $stream,
    template: fopen('template.json', 'r')
)
```

### XML

The `XmlObjectReader` provides a configured XML reader that returns a series of `SimpleXMLElement` objects.

```php
$reader = new XmlObjectReader(
    stream: $stream,
    path: '/to/elements/item'
)
```

The reader above will return a `SimpleXMlElement` for each node found in `<to><elements><item>...</item></elements></to>`.

A `XMLObjectWriter` writes a series of `SimpleXmlElement` objects into a XML stream.

```php
$writer = XmlObjectWriter(
    stream: $stream,
    template: fopen('template.xml', 'r')
)
```

> NOTE: XML declaration is set by the writer and can not be overridden by using a template.

## Encoding

Input encoding normalization should happen before parsing, preferably with PHP stream filters.

```php
stream_filter_append(
    $stream,
    'convert.iconv.ISO-8859-1/UTF-8'
);
```

Parsers should receive input in the encoding they expect, normally UTF-8. Encoding conversion is intentionally kept outside readers, parsers and deserializers.

## Extending the package

The package is designed so additional record formats can be added without changing the higher-level read and write APIs.

For example, a fixed-width reader can be composed as:

```text
stream
→ FixedLineParser
→ FixedLineIterator
→ StringChunkDeserializer
→ StreamObjectReader
```

The preconfigured readers are convenience wrappers around the same lower-level components. Advanced users can compose `StreamObjectReader` directly with custom iterators and deserializers.

See the documentation for more detailed extension examples.

## Documentation

- [Architecture](docs/architecture.md)
- [Reading](docs/reading.md)
- [Writing](docs/writing.md)
- [Transformers](docs/transformers.md)
- [Encoding and errors](docs/encoding-and-errors.md)
- [Extending](docs/extending.md)