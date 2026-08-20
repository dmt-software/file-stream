# File Stream

## Processor

```php
use DMT\FileStream\Processor; 
use DMT\FileStream\Reader\ObjectReader; 
use DMT\FileStream\Reader\Pointer\XmlSimplePathPointer; 
use DMT\FileStream\Reader\Stream\XmlElementIterator; 
use DMT\FileStream\Serialization\SimpleXmlDeserializer; 
use DMT\XmlParser\Parser; 
use DMT\XmlParser\Source\FileParser; 
use DMT\XmlParser\Tokenizer\XmlReaderTokenizer; 
        
$parser = new Parser(new XmlReaderTokenizer(new FileParser('programming.xml')));

$reader = new ObjectReader(
    new XmlElementIterator($parser),
    new XmlSimplePathPointer($parser, resultPath: '/languages/language', headerPath: '/summary'),
    new SimpleXmlDeserializer(),
);

$processor = new Processor($reader);
$processor->validate(fn (SimpleXMLElement $header) => $header->name == 'programming-languages');
$processor->limit(0, 2);
$processor->filter(fn (SimpleXMLElement $row) => $row->since < 2000);

foreach ($processor->getResults() as $key => $language) {
    printf('%d: %-10s %d' . PHP_EOL, $key, $language->name, $language->since);
}

// outputs something like:
// 0: Javascript 1995
// 1: PHP        1995
```


