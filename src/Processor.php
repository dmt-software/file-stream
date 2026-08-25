<?php

declare(strict_types=1);

namespace DMT\FileStream;

use CallbackFilterIterator;
use DMT\FileStream\Exception\ValidationException;
use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Filter\CallbackFilter;
use DMT\FileStream\Filter\FilterInterface;
use DMT\FileStream\Reader\ReaderInterface;
use DMT\FileStream\Validator\CallbackValidator;
use DMT\FileStream\Validator\ValidatorInterface;
use LimitIterator;

class Processor
{
    /**
     * @var list<FilterInterface>
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

    public function __construct(
        private ReaderInterface $reader,
    ) {
    }

    /**
     * Validate the file stream data using the stream header.
     *
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function validate(ValidatorInterface|callable $function): void
    {
        if (!$function instanceof ValidatorInterface) {
            $function = new CallbackValidator($function(...));
        }

        $function->validate($this->reader->getHeader());
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
     * Like execution a query on a database, the limit is applied after the filter(s).
     *
     * @return iterable<int, mixed>
     */
    public function getResults(): Iterable
    {
        $iterator = $this->reader->getResults();

        foreach ($this->filters as $filter) {
            $iterator = new CallbackFilterIterator($iterator, $filter);
        }

        yield from new LimitIterator($iterator, $this->offset, $this->limit);
    }
}
