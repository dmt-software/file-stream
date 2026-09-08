<?php

declare(strict_types=1);

namespace DMT\FileStream\Format\Xml\Writer;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ParserException;
use DMT\FileStream\Path\PathInterface;
use DMT\FileStream\Writer\TemplateParserInterface;
use DMT\XmlParser\Node\Element;
use Throwable;
use XMLReader;
use XMLWriter;

final class XmlTemplateParser implements TemplateParserInterface
{
    private array $stack = [];
    private bool $pathFound = false;

    public function __construct(
        private readonly XMLReader $reader,
        private readonly PathInterface $path,
        private readonly XMLWriter $writer,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function copyToPath(): void
    {
        try {
            while ($this->reader->read()) {
                if ($this->path->matchesPath($this->stack)) {
                    $this->writer->text('');
                    $this->pathFound = true;
                    return;
                }

                $this->copyNode();
            }
        } catch (Throwable $throwable) {
            throw new ParserException(
                'Unable to parse XML template',
                previous: $throwable
            );
        }

        throw new NotFoundException('Template placeholder not found');
    }

    /**
     * @inheritDoc
     */
    public function copyRemainder(): void
    {
        if (!$this->pathFound) {
            throw new ParserException('Template placeholder not found');
        }

        try {
            do {
                $this->copyNode();
            } while ($this->reader->read());
        } catch (Throwable $throwable) {
            throw new ParserException(
                'Unable to parse XML template',
                previous: $throwable
            );
        }
    }

    private function copyNode(): void
    {
        $this->stack = array_slice($this->stack, 0, $this->reader->depth);

        match ($this->reader->nodeType) {
            XMLReader::ELEMENT => $this->copyElement(),
            XMLReader::END_ELEMENT => $this->copyEndElement(),
            XMLReader::TEXT => $this->copyText(),
            XMLReader::CDATA => $this->copyCdata(),
            XMLReader::COMMENT => $this->copyComment(),
            default => null,
        };
    }

    private function copyElement(): void
    {
        $this->writer->startElement(
            $this->reader->name
        );

        $this->stack[$this->reader->depth] = new Element($this->reader->localName);

        if ($this->reader->hasAttributes) {
            while ($this->reader->moveToNextAttribute()) {
                $this->writer->writeAttribute(
                    $this->reader->name,
                    $this->reader->value
                );
            }

            $this->reader->moveToElement();
        }

        if ($this->reader->isEmptyElement) {
            $this->writer->endElement();
        }
    }

    private function copyEndElement(): void
    {
        $this->writer->endElement();
    }

    private function copyText(): void
    {
        $this->writer->text($this->reader->value);
    }

    private function copyCdata(): void
    {
        $this->writer->writeCdata($this->reader->value);
    }

    private function copyComment(): void
    {
        $this->writer->writeComment($this->reader->value);
    }
}
