<?php

declare(strict_types=1);

namespace DMT\FileStream;

use DMT\FileStream\Format\Xml\Serialization\SimpleXmlSerializer;
use DMT\FileStream\Format\Xml\Writer\XmlStreamWriter;
use DMT\FileStream\Format\Xml\Writer\XmlTemplateParser;
use DMT\FileStream\Format\Xml\XmlPath;
use DMT\FileStream\Stream\ReadableResourceStream;
use DMT\FileStream\Stream\ReadableStreamInterface;
use DMT\FileStream\Stream\WritableResourceStream;
use DMT\FileStream\Stream\WritableStreamInterface;
use DMT\FileStream\Writer\ObjectWriterInterface;
use DMT\FileStream\Writer\StreamObjectWriter;
use SimpleXMLElement;
use XMLReader;
use XMLWriter;

/**
 * Writes SimpleXMLElement instances as XML.
 *
 * @implements ObjectWriterInterface<SimpleXMLElement>
 */
final class XmlObjectWriter implements ObjectWriterInterface
{
    private ?ReadableStreamInterface $template = null;

    /**
     * @param resource|WritableStreamInterface $stream
     * @param resource|ReadableStreamInterface|null $template
     */
    public function __construct(
        private mixed $stream {
            set => $value instanceof WritableStreamInterface ? $value : new WritableResourceStream($value);
        },
        mixed $template = null,
        private readonly string $path = XmlPath::ROOT_PATH
    ) {
        if (is_null($template)) {
            return;
        }
        $this->template = $template instanceof ReadableStreamInterface
            ? $template
            : new ReadableResourceStream($template);

    }

    /**
     * @param iterable<int, SimpleXMLElement> $objects
     */
    public function write(iterable $objects): void
    {
        $xmlWriter = XMLWriter::toStream($this->stream->getResource());

        $templateParser = null;
        if ($this->template) {
            $templateParser = new XmlTemplateParser(
                $xmlWriter,
                XMLReader::fromStream($this->template->getResource()),
                new XmlPath($this->path)
            );
        }

        $writer = new StreamObjectWriter(
            new XmlStreamWriter($xmlWriter, $templateParser),
            new SimpleXmlSerializer(),
        );

        $writer->write($objects);
    }
}