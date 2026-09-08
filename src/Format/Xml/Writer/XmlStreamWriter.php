<?php

declare(strict_types=1);

namespace DMT\FileStream\Format\Xml\Writer;

use DMT\FileStream\Writer\FinalizeStreamInterface;
use DMT\FileStream\Writer\PrepareStreamInterface;
use DMT\FileStream\Writer\StreamWriterInterface;
use DMT\FileStream\Writer\TemplateParserInterface;
use RuntimeException;
use XMLWriter;

final readonly class XmlStreamWriter implements
    StreamWriterInterface,
    PrepareStreamInterface,
    FinalizeStreamInterface
{
    public function __construct(
        private XMLWriter $writer,
        private ?TemplateParserInterface $template = null,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function prepare(): void
    {
        $this->writer->startDocument(encoding: 'UTF-8');

        if ($this->template) {
            $this->template->copyToPath();

            return;
        }

        $this->writer->startElement('result');
    }

    /**
     * @inheritDoc
     */
    public function write(string $data): void
    {
        if (!$this->writer->writeRaw($data)) {
            throw new RuntimeException('Unable to write complete XML data');
        }
    }

    /**
     * @inheritDoc
     */
    public function finalize(): void
    {
        if ($this->template) {
            $this->template->copyRemainder();
        } else {
            $this->writer->endElement();
        }

        $this->writer->endDocument();
    }
}
