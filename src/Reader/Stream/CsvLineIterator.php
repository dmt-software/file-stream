<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Stream;

use DMT\FileStream\Reader\Csv\CsvLineParser;
use Iterator;

/**
 * Iterates over complete CSV records parsed from a stream.
 *
 * The iterator is forward-only and preserves the record position as its key.
 * Multiline CSV fields are handled by CsvLineParser before records are exposed
 * by this iterator.
 *
 * @implements Iterator<int, string>
 */
final class CsvLineIterator implements Iterator
{
    /**
     * The current line.
     */
    private ?string $current = null;

    /**
     * The current key.
     */
    private int $key = -1;

    /**
     * Indicates if the iterator has been started.
     */
    private bool $started = false;

    public function __construct(
        private readonly CsvLineParser $parser,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function current(): string
    {
        return $this->current ?? '';
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $this->current = $this->parser->parse();

        if ($this->current !== null) {
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
        return $this->current !== null;
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
        $this->next();
    }
}
