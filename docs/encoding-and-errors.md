# Encoding and error handling

## Encoding

Encoding normalization belongs before parsing.

Prefer PHP stream filters:

```php
stream_filter_append(
    $stream,
    'convert.iconv.ISO-8859-1/UTF-8'
);
```

This keeps encoding conversion out of:

- object readers
- parsers
- deserializers
- read statements

Parsers should receive text in the encoding they expect, normally UTF-8.

## Native PHP warnings

Some underlying PHP APIs can emit warnings in addition to returning a failure value or throwing an exception.

The package does not generally alter global warning handling or libxml state. Applications remain responsible for deciding whether warnings are displayed, converted to exceptions, logged or suppressed.

Tests may suppress a single intentional native warning when testing a failure path.

## Exception policy

Use standard PHP exceptions for caller/programmer mistakes:

```text
InvalidArgumentException
```

Use package exceptions for data and processing failures:

```text
NotFoundException
ReaderException
ParserException
SerializationException
WriterException
```

`NotFoundException` is appropriate when a required template placeholder cannot be found.

## XML declarations

XML template declaration in templates is currently ignored, the writer uses `<?xml version="1.0" encoding="UTF-8"?>`.

`SimpleXmlSerializer` produces XML fragments and strips the XML declaration from `SimpleXMLElement::asXML()` output.

This keeps document framing owned by `XmlStreamWriter` and `XmlTemplateParser`.
