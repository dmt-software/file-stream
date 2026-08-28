<?php

declare(strict_types=1);

namespace DMT\FileStream;

use CallbackFilterIterator;
use DMT\FileStream\Filter\CallbackFilter;
use DMT\FileStream\Filter\FilterInterface;
use DMT\FileStream\Reader\ReaderInterface;
use Iterator;
use LimitIterator;

/**
 * @template T of object
 * @implements ReaderInterface<T>
 */
final class Processor implements ReaderInterface
{
    /**
     * @var list<FilterInterface<T>>
     */
    private array $filters = [];

    /**
     * @var int
     */
    private int $offset = 0;

    /**
     * @var int
     */
    private int $limit = -1;

    /**
     * @param ReaderInterface<T> $reader
     */
    public function __construct(private readonly ReaderInterface $reader)
    {
    }

    /**
     * Add a callback filter to the processor.
     *
     * @param FilterInterface<T>|callable(T, int): bool $filter
     */
    public function filter(FilterInterface|callable $filter): void
    {
        if (!$filter instanceof FilterInterface) {
            /** @var FilterInterface<T> $filter */
            $filter = new CallbackFilter($filter(...));
        }

        $this->filters[] = $filter;
    }

    /**
     * Set the offset and limit of the results.
     */
    public function limit(int $offset = 0, ?int $limit = null): void
    {
        $this->offset = $offset;
        $this->limit = $limit ?? -1;
    }

    /**
     * Get the results from the reader.
     *
     * Like execution a query on a database, the limit is applied after the filter(s).
     *
     * @return Iterator<int, T>
     */
    public function getResults(): Iterator
    {
        $iterator = $this->reader->getResults();

        foreach ($this->filters as $filter) {
            $iterator = new CallbackFilterIterator($iterator, $filter);
        }

        yield from new LimitIterator($iterator, $this->offset, $this->limit);
    }
}
