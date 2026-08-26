<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ReaderException;
use Iterator;

/**
 * @template H of object
 * @template T of object
 * @implements ReaderInterface<H, T>
 */
final class IterableReader implements ReaderInterface
{
    private bool $started = false;

    /**
     * @param iterable<mixed, T> $iterable
     * @param H|null $header
     */
    public function __construct(
        private iterable $iterable,
        private ?object $header = null,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getHeader(): object
    {
        return $this->header
            ?? throw new NotFoundException('Iterable does not contain a header.');
    }

    /**
     * {@inheritDoc}
     */
    public function getResults(): Iterator
    {
        if ($this->started) {
            throw new ReaderException('Cannot rewind iterable stream.');
        }

        $this->started = true;

        $key = 0;

        foreach ($this->iterable as $result) {
            yield $key++ => $result;
        }
    }
}
