# JSON

JSON documents are processed using `JsonParserFactory`, `JsonObjectParser`, `JsonDottedSlugPointer`, `JsonObjectIterator`, and `ObjectReader`.

## Example

Given:

```json
{
    "summary": {
        "name": "programming-languages"
    },
    "languages": [
        {
            "name": "Javascript",
            "since": 1995
        },
        {
            "name": "PHP",
            "since": 1995
        }
    ]
}
```

```php
use DMT\FileStream\Factory\JsonParserFactory;
use DMT\FileStream\Reader\ObjectReader;
use DMT\FileStream\Reader\Parser\JsonObjectParser;
use DMT\FileStream\Reader\Pointer\JsonDottedSlugPointer;
use DMT\FileStream\Reader\Stream\JsonObjectIterator;
use DMT\FileStream\Serialization\JsonDecodeDeserializer;

$jsonReader = (new JsonParserFactory())->fromFile('languages.json');

$parser = new JsonObjectParser($jsonReader);

$reader = new ObjectReader(
    new JsonObjectIterator($parser),
    new JsonDottedSlugPointer(
        $parser,
        resultPath: '.languages',
        headerPath: '.summary',
    ),
    new JsonDecodeDeserializer(),
);

$header = $reader->getHeader();

foreach ($reader->getResults() as $language) {
    echo $language->name;
}
```

## Factory usage

JSON parsers can be created from a file, stream, or string:

```php
$factory = new JsonParserFactory();

$factory->fromFile('data.json');
$factory->fromStream($stream);
$factory->fromString($json);
```

Factory configuration can be supplied when required:

```php
$factory = new JsonParserFactory([
    // JsonReader configuration
]);
```

## Paths

JSON pointers use dot-separated object paths.

The root JSON object has no name, so a path should start with `.` to enter that nameless object.

```text
.response.data.languages
```

### Keys containing dots

Escape a literal dot inside a JSON key:

```text
response\.data.languages
```

This can resolve:

```json
{
    "response.data": {
        "languages": []
    }
}
```

A configured path that cannot be found before the end of the document results in `NotFoundException`.

## Streaming behavior

JSON processing is forward-only.

When both a header and a result path are configured, the header is expected to occur before the results.

Once iteration has advanced, the stream cannot be rewound.
