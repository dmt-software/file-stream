<?php

declare(strict_types=1);

namespace DMT\FileStream\Writer\Parser;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ParserException;
use Throwable;
use XMLReader;
use XMLWriter;

final readonly class XmlTemplateParser implements TemplateParserInterface
{
    public function __construct(
        private XMLReader $reader,
        private XMLWriter $writer,
        private string $placeholder = self::DEFAULT_PLACEHOLDER,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function copyToPlaceholder(): void
    {
        try {
            while ($this->reader->read()) {
                if ($this->isPlaceholder()) {
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

        throw new NotFoundException(
            'Template placeholder not found'
        );
    }

    /**
     * @inheritDoc
     */
    public function copyRemainder(): void
    {
        try {
            while ($this->reader->read()) {
                $this->copyNode();
            }
        } catch (Throwable $throwable) {
            throw new ParserException(
                'Unable to parse XML template',
                previous: $throwable
            );
        }
    }

    private function isPlaceholder(): bool
    {
        return $this->reader->nodeType === XMLReader::TEXT
            && $this->reader->value === $this->placeholder;
    }

    private function copyNode(): void
    {
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
        $this->writer->text(
            $this->reader->value
        );
    }

    private function copyCdata(): void
    {
        $this->writer->writeCdata(
            $this->reader->value
        );
    }

    private function copyComment(): void
    {
        $this->writer->writeComment(
            $this->reader->value
        );
    }
}
