<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Stream;

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
     * The depth of the elements to return.
     */
    private ?int $depth = null;

    /**
     * The local name of the elements to return.
     */
    private ?string $name = null;

    /**
     * The current key.
     */
    private int $key = -1;

    /**
     * Indicates if the iterator has been started.
     */
    private bool $started = false;

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
        do {
            $node = $this->parser->parse();

            if (!$node) {
                $this->node = null;

                return;
            }

            if (!$node instanceof Element) {
                continue;
            }

            $this->node = $node;
        } while (!$this->isValidNode());

        $this->key++;
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

        /** @var Element $node */
        $node = $this->selector->moveToNode();

        $this->node = $node;
        $this->depth = $node->depth();
        $this->name = $node->localName;
        $this->key = 0;
    }

    private function isValidNode(): bool
    {
        return
            $this->node?->depth() === $this->depth
            && $this->node?->localName === $this->name
        ;
    }
}
