# Writing

## Object writers

Object writers consume an iterable of objects and serialize/write them lazily.

`StreamObjectWriter<T>` combines a serializer and a stream writer:

```text
iterable<T>
→ SerializerInterface<T>
→ StreamWriterInterface
```

If a stream writer implements `PrepareStreamInterface` and/or `FinalizeStreamInterface`, `StreamObjectWriter` invokes those lifecycle methods around the streamed objects.

## WritePipeline

`WritePipeline<T, R>` connects a reader and writer:

```php
$pipeline = new WritePipeline($writer);
$pipeline->write($reader->getResults());
```

### Optional transformation

When the reader and writer use different object representations:

```php
$pipeline->transform(
    new ToArrayObjectTransformer()
);

$pipeline->write($reader->getResults());
```

Flow:

```text
ObjectReaderInterface<T>
→ TransformerInterface<T, R>
→ ObjectWriterInterface<R>
```

Without a transformer:

```text
ObjectReaderInterface<T>
→ ObjectWriterInterface<T>
```

The implementation remains lazy; the reader is consumed as the writer consumes the iterable.

## PDO example

```php
$statement = $pdo->query(
    'SELECT id, name, email FROM users'
);
$statement->setFetchMode(PDO::FETCH_OBJ);

$pipeline = new WritePipeline($csvWriter);

$pipeline->transform(new ToArrayObjectTransformer());
$pipeline->write($statement);
```

## CSV writing

CSV writing separates column selection from serialization.

```text
ArrayObject
→ ColumnStrategy
→ list<scalar|null>
→ StringPutCsvSerializer
→ CsvStreamWriter
```

`CsvStreamWriter` owns the line ending. The serializer only creates the CSV record.

## JSON writing

`JsonEncodeSerializer` serializes `stdClass`.

`JsonStreamWriter` writes a JSON collection by default:

```json
[{"id":1},{"id":2}]
```

With a template:

```json
{"meta":{"version":1},"items":[{{items}}]}
```

the template parser writes everything before `{{items}}`, streamed objects are inserted, and the remainder is copied afterward.

## XML writing

`SimpleXmlSerializer` returns XML fragments without an XML declaration.

`XmlStreamWriter` uses `XMLWriter::writeRaw()` so that both the template parser and stream writer share the same internal `XMLWriter`.

Without a template:

```xml
<result>
    <item>...</item>
</result>
```

With a template, framing is delegated to `XmlTemplateParser`:

```xml
<result>
    <meta version="1"/>
    <items>{{items}}</items>
</result>
```
