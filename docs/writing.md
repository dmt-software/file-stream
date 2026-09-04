# Writing

## ObjectWriterInterface

All object writers consume an iterable of objects:

```php
interface ObjectWriterInterface
{
    /** @param iterable<int, T> $objects */
    public function write(iterable $objects): void;
}
```

This is the common write-side abstraction used by preconfigured writers, `StreamObjectWriter` and `WritePipeline`.

## Preconfigured writers

For common formats, use the preconfigured object writers:

```text
CsvObjectWriter   ← ArrayObject
JsonObjectWriter  ← stdClass
XmlObjectWriter   ← SimpleXMLElement
```

They configure the required serializer and stream writer internally.

Use `StreamObjectWriter` directly when custom serialization or framing is required.

## StreamObjectWriter

`StreamObjectWriter<T>` combines a serializer and stream writer:

```text
iterable<T>
→ SerializerInterface<T>
→ StreamWriterInterface
→ output
```

If its stream writer implements `PrepareStreamInterface` and/or `FinalizeStreamInterface`, `StreamObjectWriter` invokes those lifecycle methods around the object stream.

## WritePipeline

`WritePipeline<T, R>` is itself an `ObjectWriterInterface`.

It decorates another object writer and optionally transforms objects before forwarding them.

```php
$writer = new WritePipeline(
    writer: $csvWriter,
);
```

When no transformer is configured, input objects are forwarded unchanged.

### Transforming before writing

```php
$writer = (new WritePipeline(
    writer: $csvWriter,
))->transform(
    new ToArrayObjectTransformer()
);
```

The pipeline is then used exactly like any other object writer:

```php
$writer->write(
    $reader->getResults()
);
```

Flow:

```text
iterable<T>
→ WritePipeline<T, R>
→ TransformerInterface<T, R>
→ ObjectWriterInterface<R>
```

Transformation remains lazy; objects are transformed only as the wrapped writer consumes them.

Because `WritePipeline` implements `ObjectWriterInterface`, it does not introduce a separate execution API.

## PDO to CSV

```php
$statement = $pdo->query(
    'SELECT id, name, email FROM users'
);
$statement->setFetchMode(PDO::FETCH_OBJ);

$reader = new IterableObjectReader($statement);

$writer = (new WritePipeline(
    writer: $csvWriter,
))->transform(
    new ToArrayObjectTransformer()
);

$writer->write(
    $reader->getResults()
);
```

No intermediate array is required.

## CSV writing

```text
ArrayObject
→ ColumnStrategy
→ list<scalar|null>
→ StringPutCsvSerializer
→ CsvStreamWriter
```

`CsvStreamWriter` owns the record line ending. `StringPutCsvSerializer` only serializes the CSV record.

## JSON writing

`JsonEncodeSerializer` serializes `stdClass`.

`JsonStreamWriter` writes a JSON collection by default:

```json
[{"id":1},{"id":2}]
```

## XML writing

`SimpleXmlSerializer` serializes `SimpleXMLElement` as an XML fragment without an XML declaration.

`XmlStreamWriter` uses `XMLWriter::writeRaw()`, allowing it and `XmlTemplateParser` to share the same internal `XMLWriter`.

Without a template:

```xml
<result>
    <item>...</item>
</result>
```

## Templates

Template parsers provide document framing around streamed objects.

```php
interface TemplateParserInterface
{
    public const string DEFAULT_PLACEHOLDER = '{{items}}';

    public function copyToPlaceholder(): void;

    public function copyRemainder(): void;
}
```

The placeholder is consumed and is not copied to the output.

> NOTE: template parsers might be changed to accept a path like reader do. 

### JSON templates

```json
{
  "meta": {
    "version": 1
  },
  "items": [{{items}}]
}
```

### XML templates

```xml
<root>
    <meta>Example</meta>
    <items>{{items}}</items>
</root>
```

`XmlTemplateParser` copies XML nodes up to the placeholder. `XmlStreamWriter` inserts serialized XML fragments through the shared `XMLWriter`, after which the parser copies the remainder.
