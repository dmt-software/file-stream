<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Stream;

use DMT\FileStream\Exception\ReaderException;
use DMT\FileStream\Reader\Parser\JsonObjectNode;
use DMT\FileStream\Reader\Parser\JsonObjectParser;
use DMT\FileStream\Stream\ParserInterface;
use pcrov\JsonReader\Exception;
use pcrov\JsonReader\JsonReader;

final class JsonObjectIterator implements StreamIterator
{
    private int $key = 0;
    private ?JsonObjectNode $node = null;

    public function __construct(private readonly JsonObjectParser $parser)
    {
    }

    public function key(): int
    {
        return $this->key;
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        try {
            do {
                $node = $this->parser->parse();

                if (!$node) {
                    $this->node = null;
                }

                $this->node ??= $node;
            } while (!($node->depth == $this->node?->depth && $node->name == $this->node?->name));
        } catch (Exception $exception) {
            throw new ReaderException('Error while reading JSON', previous: $exception);
        } finally {
            $this->key++;
        }
    }

    /**
     * @inheritDoc
     */
    public function current(): string
    {
        return (string) $this->parser->parseValue();
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        // no rewind, keep the file pointer where it is.
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return $this->key === 0 || $this->node !== null;
    }
}
