<?php

declare(strict_types=1);

namespace DMT\FileStream\Format\Xml\Reader;

use DMT\FileStream\Exception\ParserException;
use Throwable;
use XMLReader;

class XmlElementNodeParser
{
    private ?XmlElementNode $current = null;

    public function __construct(private XMLReader $reader)
    {
    }

    public function parse(): ?XmlElementNode
    {
        try {
            while ($this->reader->read()) {
                if ($this->reader->nodeType === XMLReader::ELEMENT) {
                    return $this->current = new XmlElementNode(
                        $this->reader->depth,
                        $this->reader->name,
                    );
                }
            }

            return null;
        } catch (Throwable $throwable) {
            throw new ParserException('Unable to parse XML', previous: $throwable);
        }
    }

    public function parseXml(): XmlElementNode
    {
        $this->current->value = $this->reader->readOuterXml();

        return $this->current;
    }
}
