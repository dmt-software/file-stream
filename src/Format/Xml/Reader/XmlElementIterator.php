<?php

declare(strict_types=1);

namespace DMT\FileStream\Format\Xml\Reader;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Reader\PathSelectorInterface;
use Iterator;

/**
 * Iterates over XML elements matching the selected element.
 *
 * The selector determines the first matching element. From that point onward,
 * every element with the same depth and local name is returned, even when
 * matching elements are separated by other XML structures.
 *
 * The iterator is forward-only and cannot be restarted once iteration has
 * begun.
 *
 * @implements Iterator<int, string>
 */
final class XmlElementIterator implements Iterator
{
    /**
     * The last element parsed.
     */
    private ?XmlElementNode $node = null;

    /**
     * The current key.
     */
    private int $key = -1;

    /**
     * Indicates if the iterator has been started.
     */
    private bool $started = false;

    /**
     * @param PathSelectorInterface<XmlElementNode> $selector
     */
    public function __construct(
        private readonly XmlElementNodeParser $parser,
        private readonly PathSelectorInterface $selector,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function current(): string
    {
        return $this->node?->value ?? '';
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        try {
            $this->selector->moveToNode();

            $this->node = $this->parser->parseXml();
            $this->key++;
        } catch (NotFoundException) {
            $this->node = null;
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
        return $this->node !== null;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        if ($this->started) {
            return;
        }

        $this->started = true;

        $this->selector->moveToNode();

        $this->node = $this->parser->parseXml();
        $this->key = 0;
    }
}
