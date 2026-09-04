# Reading

## ObjectReaderInterface

All object readers expose the same lazy result contract:

```php
interface ObjectReaderInterface
{
    /** @return Iterator<int, T> */
    public function getResults(): Iterator;
}
```

Keys are integers but do not have to be sequential. Readers preserve source keys where possible.

## Preconfigured readers

For common formats, use the preconfigured readers:

```text
CsvObjectReader   → ArrayObject
JsonObjectReader  → stdClass
XmlObjectReader   → SimpleXMLElement
```

They configure the required parser, iterator and deserializer internally.

Use `StreamObjectReader` directly when custom parsing or deserialization is required.

## IterableObjectReader

`IterableObjectReader<T>` adapts an existing iterable of objects.

The iterable is expected to be homogeneous: every yielded object must be compatible with the same generic type `T`. This is a static-analysis and documentation contract rather than runtime validation.

```php
$statement = $pdo->query('SELECT id, name FROM users');
$statement->setFetchMode(PDO::FETCH_OBJ);

$reader = new IterableObjectReader($statement);
```

## ReadStatement

`ReadStatement<T>` decorates another `ObjectReaderInterface<T>` and implements the same interface.

```php
$statement = (new ReadStatement($reader))
    ->filter(...)
    ->limit(10, 100)
    ->modify(...);
```

Processing order:

```text
filters
→ offset / limit
→ modifiers
```

## Reading into a writer

Readers and writers meet through the result iterable:

```php
$writer->write(
    $statement->getResults()
);
```

If the output format requires another object representation, decorate the writer with a `WritePipeline`:

```php
$writer = (new WritePipeline($csvWriter))
    ->transform(
        new ToArrayObjectTransformer()
    );

$writer->write(
    $statement->getResults()
);
```
