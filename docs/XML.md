# XML

XML documents are processed using `XmlParserFactory`, `XmlSimplePathPointer`, `XmlElementIterator`, `ObjectReader`, and `SimpleXmlDeserializer`.

## Example

Given:

```xml
<?xml version="1.0"?>
<programming>
    <summary>
        <name>programming-languages</name>
    </summary>
    <languages>
        <language>
            <name>Javascript</name>
            <since>1995</since>
        </language>

        <language>
            <name>PHP</name>
            <since>1995</since>
        </language>
    </languages>
</programming>
```

```php
use DMT\FileStream\Factory\XmlParserFactory;
use DMT\FileStream\Reader\ObjectReader;
use DMT\FileStream\Reader\Pointer\XmlSimplePathPointer;
use DMT\FileStream\Reader\Stream\XmlElementIterator;
use DMT\FileStream\Serialization\SimpleXmlDeserializer;

$parser = new XmlParserFactory()->fromFile('languages.xml');

$reader = new ObjectReader(
    new XmlElementIterator($parser),
    new XmlSimplePathPointer(
        $parser,
        resultPath: '/programming/languages/language',
        headerPath: '/programming/summary',
    ),
    new SimpleXmlDeserializer(),
);

$header = $reader->getHeader();

foreach ($reader->getResults() as $language) {
    echo $language->name;
}
```

Results are returned as `SimpleXMLElement` instances.

## Factory usage

XML parsers can be created from a file, stream, or string:

```php
$factory = new XmlParserFactory();

$factory->fromFile('data.xml');
$factory->fromStream($stream);
$factory->fromString($xml);
```

Factory configuration can be supplied when required:

```php
$factory = new XmlParserFactory([
    'encoding' => 'UTF-8',
    'flags' => 0,
]);
```

## Paths

`XmlSimplePathPointer` uses simple slash-separated element paths.

For example:

```text
/programming/languages/language
```

matches:

```xml
<programming>
    <languages>
        <language />
    </languages>
</programming>
```

### Match any element

Use `.` to match any element at that position, similar to a wildcard.

For example:

```text
/./language
```

matches a `language` element below any root element.

This can be useful when the outer element name is not important.

### Namespaces and Clark notation

Namespaced elements can be addressed with Clark notation:

```text
/{namespace.uri}element
```

For example:

```text
/{https://example.com/schema}root/{https://example.com/schema}item
```

can match:

```xml
<root xmlns="https://example.com/schema">
    <item />
</root>
```

Clark notation avoids depending on the namespace prefix used in the source document.


## Streaming behavior

XML processing is forward-only.

When both a header and a result path are configured, the header is expected to occur before the results.

Once iteration has advanced, the stream cannot be rewound.

A configured path that cannot be found before the end of the document results in `NotFoundException`.
