<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Stream;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Reader\Selector\PathSelectorInterface;
use DMT\XmlParser\Node\Element;
use DMT\XmlParser\Parser;
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
    private ?Element $node = null;

    /**
     * The current key.
     */
    private int $key = -1;

    /**
     * Indicates if the iterator has been started.
     */
    private bool $started = false;

    /**
     * @param PathSelectorInterface<Element> $selector
     */
    public function __construct(
        private readonly Parser $parser,
        private readonly PathSelectorInterface $selector,
    ) {
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
            $node = $this->selector->moveToNode();

            $this->node = $node;
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

        $node = $this->selector->moveToNode();

        $this->node = $node;
        $this->key = 0;
    }
}
