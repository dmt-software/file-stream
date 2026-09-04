# Reading

## ObjectReaderInterface

All object readers expose the same lazy result contract:

```php
/**
 * @template T of object
 */
interface ObjectReaderInterface
{
    /**
     * @return Iterator<int, T>
     */
    public function getResults(): Iterator;
}
```

Readers preserve source keys where possible, this way you can trace the result back to the original source of the data.

## StreamObjectReader

`StreamObjectReader<T>` combines an `Iterator<int, string>` with a `DeserializerInterface<T>`. Each serialized value is deserialized lazily.

```php
$reader = new StreamObjectReader(
    iterator: $iterator,
    deserializer: $deserializer,
);
```

## IterableObjectReader

`IterableObjectReader<T>` adapts an existing iterable into an object reader.

The iterable is expected to be homogeneous: every yielded object must be compatible with the same generic type `T`. This is a static-analysis and documentation contract rather than runtime validation.

```php
$statement = $pdo->query('SELECT id, name FROM users');
$statement->setFetchMode(PDO::FETCH_OBJ);

$reader = new IterableObjectReader($statement);
```

## ReadStatement

`ReadStatement<T>` implements `ObjectReaderInterface<T>` and decorates another reader.

```php
$statement = new ReadStatement($reader);
$statement
    ->filter(...)
    ->limit(0, 100)
    ->modify(...);

foreach ($statement->getResults() as $object) {
    // ...
}
```

Processing order:

```text
filters
→ offset / limit
→ modifiers
```

### Filters

A filter decides whether an object remains in the stream:

```text
Filter<T>
(T, key) → bool
```

### Modifiers

A modifier keeps the same generic type:

```text
Modifier<T>
(T, key) → T
```

A modifier may mutate an object or return a replacement object compatible with `T`.

### Composition

Because a statement is itself an object reader, it can be passed directly into the write side:

```php
$statement = new ReadStatement($reader);
$statement->filter(...);

$pipeline = new WritePipeline(
    reader: $statement,
    writer: $writer,
);

$pipeline->execute();
```
