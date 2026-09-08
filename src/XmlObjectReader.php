<?php

declare(strict_types=1);

namespace DMT\FileStream;

use DMT\FileStream\Exception\ReaderException;
use DMT\FileStream\Format\Xml\Reader\XmlElementIterator;
use DMT\FileStream\Format\Xml\Reader\XmlElementPathSelector;
use DMT\FileStream\Format\Xml\Serialization\SimpleXmlDeserializer;
use DMT\FileStream\Format\Xml\XmlPath;
use DMT\FileStream\Path\PathInterface;
use DMT\FileStream\Reader\ObjectReaderInterface;
use DMT\FileStream\Reader\StreamObjectReader;
use DMT\FileStream\Stream\ReadableResourceStream;
use DMT\FileStream\Stream\ReadableStreamInterface;
use DMT\XmlParser\Parser;
use DMT\XmlParser\Source\StreamParser;
use DMT\XmlParser\Tokenizer\XmlReaderTokenizer;
use InvalidArgumentException;
use Iterator;
use SimpleXMLElement;

/**
 * Reads selected XML elements as SimpleXMLElement instances.
 *
 * @implements ObjectReaderInterface<SimpleXMLElement>
 */
final class XmlObjectReader implements ObjectReaderInterface
{
    private bool $started = false;

    /**
     * @param resource|ReadableStreamInterface $stream
     * @param string|PathInterface $path
     */
    public function __construct(
        private mixed $stream {
            set => $value instanceof ReadableStreamInterface ? $value : new ReadableResourceStream($value);
        },
        private string|PathInterface $path = XmlPath::ROOT_PATH {
            set => is_string($value) ? new XmlPath($value) : $value;
        },
        private readonly int $options = 0,
        private readonly ?string $namespace = null,
    ) {
        if (!$this->stream->isReadable()) {
            throw new InvalidArgumentException('Stream is not readable');
        }
    }

    /**
     * @return Iterator<int, SimpleXMLElement>
     */
    public function getResults(): Iterator
    {
        if ($this->stream->isSeekable()) {
            $this->stream->rewind();
            $this->started = false;
        }

        if ($this->started) {
            throw new ReaderException('Stream can not be rewind');
        }

        $parser = new Parser(new XmlReaderTokenizer(new StreamParser($this->stream->getResource())));
        $reader = new StreamObjectReader(
            new XmlElementIterator($parser, new XmlElementPathSelector($parser, $this->path)),
            new SimpleXmlDeserializer($this->options, $this->namespace),
        );

        $this->started = true;

        return $reader->getResults();
    }
}
