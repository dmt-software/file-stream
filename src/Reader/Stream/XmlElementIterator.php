<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Stream;

use DMT\FileStream\Exception\ReaderException;
use DMT\XmlParser\Node\Element;
use DMT\XmlParser\Node\ElementNode;
use DMT\XmlParser\Parser;
use RuntimeException;

class XmlElementIterator implements StreamIterator
{
    private int $key = 0;
    private ?Element $element = null;

    public function __construct(private readonly Parser $parser)
    {
    }

    /**
     * @inheritDoc
     */
    public function current(): string
    {
        return $this->parser->parseXml();
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        try {
            do {
                $current = $this->parser->parse();

                if (!$current) {
                    break;
                }

                $this->element ??= $current;
            } while (!$this->isValidElement($current));
        } catch (RuntimeException $exception) {
            throw new ReaderException(sprintf('Unable to read line %s', $this->key), previous: $exception);
        } finally {
            $this->key++;
        }
    }

    /**
     * @inheritDoc
     */
    public function key(): int
    {
        return $this->key;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return $this->parser->parseXml() !== '';
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        if ($this->key > 0) {
            throw new ReaderException('Cannot rewind XML stream.');
        }
    }

    private function isValidElement(Element|ElementNode $current): bool
    {
        return $current->name === $this->element->name
            && $current->namespace === $this->element->namespace
            && $current->depth() === $this->element->depth();
    }
}