<?php

declare(strict_types=1);

namespace DMT\FileStream\Structured\Template;

use DMT\FileStream\Stream\WritableStreamInterface;
use DMT\FileStream\Stream\XmlWriterStream;
use LogicException;

/**
 * Writes XML template content around a configured insertion point.
 *
 * The configured path identifies the element that will contain the serialized
 * values written between the template prefix and suffix. The values themselves
 * are supplied by the caller and are not interpreted or validated by this
 * handler.
 *
 * @implements TemplateHandlerInterface<XmlWriterStream>
 */
final class XmlRootElementTemplateHandler implements TemplateHandlerInterface
{
    /**
     * Indicates if the prefix has already been written.
     */
    private bool $prefixWritten = false;

    public function __construct(private string $rootElement = 'Results')
    {
    }

    public function writePrefix(WritableStreamInterface $output): void
    {
        if (!$output instanceof XmlWriterStream) {
            throw new LogicException('XML template requires a XmlWriterStream');
        }

        if ($this->prefixWritten) {
            throw new LogicException('XML template prefix was already written');
        }

        $this->prefixWritten = true;

        $output->getStream()->startDocument('1.0', 'UTF-8');
        $output->getStream()->startElement($this->rootElement);
        $output->getStream()->text(PHP_EOL);

        $output->flush();
    }

    public function writeSuffix(WritableStreamInterface $output): void
    {
        if (!$output instanceof XmlWriterStream) {
            throw new LogicException('XML template requires a XmlWriterStream');
        }

        if (!$this->prefixWritten) {
            throw new LogicException('XML template prefix was not written');
        }

        $output->getStream()->endElement();
        $output->getStream()->endDocument();

        $output->flush();
    }
}
