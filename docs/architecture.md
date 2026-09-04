# Architecture

The package separates reading, read-side processing, transformation and writing into small composable abstractions.

```text
source
  ↓
ObjectReaderInterface<T>
  ↓
ReadStatement<T>
  ↓
iterable<T>
  ↓
WritePipeline<T, R>
  ↓
ObjectWriterInterface<T|R>
  ↓
output
```

The read and write sides deliberately meet at `iterable<T>`.

## ObjectReaderInterface

`ObjectReaderInterface<T>` is the common read-side abstraction.

Readers expose objects lazily through `getResults()`.

A `PDOStatement`, generator or other iterable source can be adapted through `IterableObjectReader`.

## ReadStatement

`ReadStatement<T>` is a reader decorator. It accepts another `ObjectReaderInterface<T>` and exposes the processed 
results through the same interface.

```text
ObjectReaderInterface<T>
→ filters
→ offset / limit
→ modifiers
→ Iterator<int, T>
```

Because it implements `ObjectReaderInterface<T>`, statements can be nested or used anywhere another object reader is 
expected.

## ObjectWriterInterface

`ObjectWriterInterface<T>` is the common write-side abstraction.

```php
interface ObjectWriterInterface
{
    /** @param iterable<int, T> $objects */
    public function write(iterable $objects): void;
}
```

## WritePipeline

`WritePipeline<T, R>` is a writer decorator.

It implements `ObjectWriterInterface`, wraps another object writer and can optionally transform each input object before
delegating it.

Without a transformer:

```text
iterable<T>
→ WritePipeline<T, T>
→ ObjectWriterInterface<T>
```

With a transformer:

```text
iterable<T>
→ TransformerInterface<T, R>
→ ObjectWriterInterface<R>
```

The pipeline remains lazy: it passes a generator to the wrapped writer instead of collecting the transformed objects 
first.

This means `WritePipeline` can itself be used anywhere an `ObjectWriterInterface` is accepted.

## Transformer

A transformer changes object representation:

```text
Transformer<T, R>
T → R
```

Transformers are independent from the writer namespace because object conversion is not inherently a writing concern.

## StreamObjectWriter

`StreamObjectWriter<T>` combines serialization and format-specific stream output:

```text
iterable<T>
→ SerializerInterface<T>
→ StreamWriterInterface
→ output
```

If the stream writer implements `PrepareStreamInterface` or `FinalizeStreamInterface`, those lifecycle operations are 
invoked around the object stream.

## Template parsers

Template parsers only copy framing content around the insertion point:

```text
copyToPlaceholder()
        ↓
 streamed objects
        ↓
copyRemainder()
```

JSON copies template bytes. XML uses `XMLReader` while writing through the same `XMLWriter` instance used by 
`XmlStreamWriter`.