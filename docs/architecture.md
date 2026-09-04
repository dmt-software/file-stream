# Architecture

The package separates source reading, object processing, transformation and output writing.

```text
ObjectReaderInterface<T>
        ↓
ReadStatement<T>
(filter / offset / limit / modify)
        ↓
WritePipeline<T, R>
(optional transform)
        ↓
ObjectWriterInterface<R>
```

## ObjectReaderInterface

`ObjectReaderInterface<T>` is the common source abstraction.

Implementations include stream-backed readers and iterable-backed readers. A `ReadStatement<T>` also implements the same interface, which allows processed reads to be fed directly into a `WritePipeline`.

This keeps source adaptation outside the pipeline. For example, a `PDOStatement` can first be wrapped in an `IterableObjectReader`.

## ReadStatement

`ReadStatement<T>` decorates another object reader.

It is responsible for read-side processing:

```text
reader
→ filters
→ offset / limit
→ modifiers
→ Iterator<T>
```

A modifier preserves the object type:

```text
Modifier<T>
T → T
```

Original integer reader keys are preserved.

## WritePipeline

`WritePipeline<T, R>` connects a reader to a writer.

```text
ObjectReaderInterface<T>
→ optional Transformer<T, R>
→ ObjectWriterInterface<R>
```

The pipeline does not collect objects in memory. It passes a lazy iterable to the writer.

Without a transformer, the reader object type must already be compatible with the writer object type.

## Transformer

A transformer changes representation:

```text
Transformer<T, R>
T → R
```

Transformers are intentionally independent from the writer namespace because they are general object conversion components.

## Stream writers

Format-specific stream writers own framing behavior.

JSON:

```text
prepare  → [
write    → object[,object...]
finalize → ]
```

XML without a template:

```text
prepare  → <result>
write    → XML fragments
finalize → </result>
```

With a template, `prepare()` and `finalize()` delegate to a `TemplateParserInterface`.

## Template parsers

Template parsers do not serialize objects. They only copy framing content around the insertion point.

```text
copyToPlaceholder()
        ↓
 streamed objects
        ↓
copyRemainder()
```

JSON operates on template bytes. XML uses `XMLReader` together with the same `XMLWriter` instance used by `XmlStreamWriter`.
