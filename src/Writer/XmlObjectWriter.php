<?php

declare(strict_types=1);

namespace DMT\FileStream\Writer;

use DMT\FileStream\Format\Xml\Serialization\SimpleXmlSerializer;
use DMT\FileStream\Format\Xml\Writer\XmlStreamWriter;
use DMT\FileStream\Format\Xml\Writer\XmlTemplateParser;
use DMT\FileStream\Format\Xml\XmlPath;
use InvalidArgumentException;
use SimpleXMLElement;
use XMLReader;
use XMLWriter;

/**
 * Writes SimpleXMLElement instances as XML.
 *
 * @implements ObjectWriterInterface<SimpleXMLElement>
 */
final readonly class XmlObjectWriter implements ObjectWriterInterface
{
    private StreamObjectWriter $writer;

    /**
     * @param resource $stream
     * @param resource|null $template
     */
    public function __construct(
        mixed $stream,
        mixed $template = null,
        string $path = XmlPath::ROOT_PATH,
    ) {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Stream must be a resource');
        }

        if (!is_null($template)  && !is_resource($template)) {
            throw new InvalidArgumentException('Template must be a resource');
        }

        $xmlWriter = XMLWriter::toStream($stream);

        if ($template) {
            $template = new XmlTemplateParser(
                XMLReader::fromStream($template),
                new XmlPath($path),
                $xmlWriter,
            );
        }

        $this->writer = new StreamObjectWriter(
            new XmlStreamWriter($xmlWriter, $template),
            new SimpleXmlSerializer(),
        );
    }

    /**
     * @param iterable<int, SimpleXMLElement> $objects
     */
    public function write(iterable $objects): void
    {
        $this->writer->write($objects);
    }
}