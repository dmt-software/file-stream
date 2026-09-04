# Extending the package

## An Example with fixed-width records

A fixed-width reader can be composed from the same low-level abstractions used by the built-in formats:

```text
stream
→ FixedLineParser
→ FixedLineIterator
→ StringChunkDeserializer
→ StreamObjectReader
```

The parser determines record boundaries, the iterator exposes complete records, and the deserializer maps fixed string 
ranges to object properties.

```php
$reader = new StreamObjectReader(
    iterator: new FixedLineIterator(
        parser: new FixedLineParser(
            stream: $stream,
            length: 80
        )
    ),
    deserializer: new StringChunkDeserializer(
        mapping: [
            'name' => [0, 10],
            'status' => [10, 1],
            'city' => [11, 20],
        ]
    )
);
```

Higher-level components do not need to know that the source uses fixed-width records. The resulting reader can be 
wrapped in `ReadStatement` like any built-in reader.

### FixedLineParser

A parser can be configured with the expected record length:

```php
$parser = new FixedLineParser(
    length: 80
);
```

Conceptually:

```php
class FixedLineParser
{
    public function __construct(
        private int $length,
    ) {
    }

    public function parse(): ?string
    {
        // Read exactly $length bytes from the stream.
        // Return null when the stream is exhausted.
    }
}
```

For a file containing:

```text
John SmithAmsterdam  ...
Jane Doe  Rotterdam  ...
```

and a record size of `80`, every call to `parse()` returns one complete 80-byte record.

The parser does not interpret individual fields.

### FixedLineIterator

The parser can be exposed through an iterator:

```php
$iterator = new FixedLineIterator(
    parser: $parser
);
```

Conceptually:

```php
class FixedLineIterator implements Iterator
{
    public function __construct(
        private FixedLineParser $parser,
    ) {
    }

    // Iterator implementation that calls
    // $parser->parse() for each record.
}
```

The iterator yields:

```text
0 => "first 80 byte record..."
1 => "second 80 byte record..."
2 => "third 80 byte record..."
```

This makes the iterator compatible with `StreamObjectReader`.

### StringChunkDeserializer

The deserializer defines how ranges inside the fixed-width record map onto properties.

For example:

```php
$deserializer = new StringChunkDeserializer(
    mapping: [
        'name' => [0, 10],
        'status' => [10, 1],
        'city' => [11, 20],
    ]
);
```

The mapping values represent:

```text
[offset, length]
```

So:

```php
[
    'name' => [0, 10],
    'status' => [10, 1],
]
```

means:

```text
bytes 0-9   → name
byte 10     → status
```

Conceptually:

```php
class StringChunkDeserializer implements DeserializerInterface
{
    public function __construct(
        private array $mapping,
    ) {
    }

    public function deserialize(string $data): object
    {
        $result = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);

        foreach ($this->mapping as $property => [$offset, $length]) {
            $result->{$property} = substr($data,$offset,$length);
        }

        return $result;
    }
}
```

Normalization such as trimming can either happen in the deserializer:

```php
$result->{$property} = trim(
    substr($data, $offset, $length)
);
```

or later through a modifier or transformer, depending on the desired responsibility.

### Composing the reader

The pieces can then be passed into a normal `StreamObjectReader`:

```php
$reader = new StreamObjectReader(
    iterator: new FixedLineIterator(
        parser: new FixedLineParser(
            stream: $stream,
            length: 80
        )
    ),
    deserializer: new StringChunkDeserializer(
        mapping: [
            'name' => [0, 10],
            'status' => [10, 1],
            'city' => [11, 20],
        ]
    )
);
```

Consumers do not need to know that the source uses fixed-width records:

```php
foreach ($reader->getResults() as $record) {
    echo $record->name;
}
```

The resulting architecture remains the same as for CSV, JSON, or XML:

```text
source format
→ string iterator
→ deserializer
→ object reader
```

This is one of the main extension points of the package: new record formats can usually be added by implementing a parser/iterator pair and an appropriate serializer or deserializer, without changing the higher-level `ReadStatement` or `WritePipeline`.
