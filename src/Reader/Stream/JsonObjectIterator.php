<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Stream;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ParserException;
use DMT\FileStream\Reader\Parser\JsonObjectNode;
use DMT\FileStream\Reader\Parser\JsonObjectNodeParser;
use DMT\FileStream\Reader\Selector\PathSelectorInterface;
use Iterator;

/**
 * Iterates over JSON objects matching the selected node.
 *
 * The selector determines the first matching node. From that point onward,
 * every object with the same depth and name is returned, even when matching
 * nodes are separated by other JSON structures.
 *
 * This is comparable to selecting multiple matching elements with an XPath
 * expression such as "/element".
 *
 * The iterator is forward-only and cannot be rewound once iteration has
 * started.
 *
 * @implements Iterator<int, string>
 */
final class JsonObjectIterator implements Iterator
{
    /**
     * The last node parsed.
     */
    private ?JsonObjectNode $node = null;

    /**
     * The current key.
     */
    private int $key = -1;

    /**
     * Indicates if the iterator has been started.
     */
    private bool $started = false;

    /**
     * @param PathSelectorInterface<JsonObjectNode> $selector
     */
    public function __construct(
        private readonly JsonObjectNodeParser $parser,
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

            $this->node = $this->parser->parseValue();
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
     * {@inheritDoc}
     *
     * @throws ParserException When the node cannot be parsed.
     * @throws NotFoundException When the node path cannot be found.
     */
    public function rewind(): void
    {
        if ($this->started) {
            return;
        }

        $this->started = true;

        $this->selector->moveToNode();

        $this->node = $this->parser->parseValue();
        $this->key = 0;
    }
}