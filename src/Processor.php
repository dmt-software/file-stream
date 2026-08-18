<?php

declare(strict_types=1);

namespace DMT\FileStream;

use CallbackFilterIterator;
use DMT\FileStream\Filter\CallbackFilter;
use DMT\FileStream\Filter\FilterInterface;
use LimitIterator;

class Processor
{
    /**
     * @var list<FilterInterface>
     */
    private array $filters = [];
    private int $offset = 0;
    private int $limit = -1;

    public function __construct(
        private ReaderInterface $reader,
    ) {
    }

    /**
     * Add a callback filter to the processor.
     */
    public function filter(FilterInterface|callable $filter): void
    {
        if (!$filter instanceof FilterInterface) {
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
     * @return iterable<int, mixed>
     */
    public function getResults(mixed $query): Iterable
    {
        $iterator = $this->reader->getResults($query);

        foreach ($this->filters as $filter) {
            $iterator = new CallbackFilterIterator($iterator, $filter);
        }

        yield from new LimitIterator($iterator, $this->offset, $this->limit);
    }
}
