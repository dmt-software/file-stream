<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader;

use DMT\FileStream\Reader\Selector\XmlElementPathSelector;
use DMT\FileStream\Reader\Stream\XmlElementIterator;
use DMT\FileStream\Serialization\SimpleXmlDeserializer;
use DMT\XmlParser\Parser;
use DMT\XmlParser\Source\StreamParser;
use DMT\XmlParser\Tokenizer\XmlReaderTokenizer;
use Iterator;
use SimpleXMLElement;

/**
 * Reads selected XML elements as SimpleXMLElement instances.
 *
 * The reader consumes the configured stream and is not rewindable.
 *
 * @implements ObjectReaderInterface<SimpleXMLElement>
 */
final readonly class XmlObjectReader implements ObjectReaderInterface
{
    public const int DEFAULT_FLAGS = 0;
    public const string DEFAULT_PATH = XmlElementPathSelector::ROOT_PATH;

    private StreamObjectReader $reader;

    /**
     * @param resource $stream
     */
    public function __construct(
        mixed $stream,
        string $path = self::DEFAULT_PATH,
        int $options = self::DEFAULT_FLAGS,
        ?string $namespace = null,
    ) {
        $parser = new Parser(new XmlReaderTokenizer(new StreamParser($stream)));

        $this->reader = new StreamObjectReader(
            new XmlElementIterator($parser, new XmlElementPathSelector($parser, $path)),
            new SimpleXmlDeserializer($options, $namespace),
        );
    }

    /**
     * @return Iterator<int, SimpleXMLElement>
     */
    public function getResults(): Iterator
    {
        return $this->reader->getResults();
    }
}
