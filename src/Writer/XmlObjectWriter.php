<?php

declare(strict_types=1);

namespace DMT\FileStream\Writer;

use DMT\FileStream\Serialization\SimpleXmlSerializer;
use DMT\FileStream\Writer\Parser\XmlTemplateParser;
use DMT\FileStream\Writer\Stream\XmlStreamWriter;
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
    ) {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Stream must be a resource');
        }

        if (!is_null($template)  && !is_resource($template)) {
            throw new InvalidArgumentException('Template must be a resource');
        }

        $xmlWriter = XMLWriter::toStream($stream);

        $templateParser = $template !== null
            ? new XmlTemplateParser(
                reader: XMLReader::fromStream($template),
                writer: $xmlWriter,
            )
            : null;

        $this->writer = new StreamObjectWriter(
            writer: new XmlStreamWriter(
                writer: $xmlWriter,
                template: $templateParser,
            ),
            serializer: new SimpleXmlSerializer(),
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