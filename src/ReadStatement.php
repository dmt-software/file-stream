<?php

declare(strict_types=1);

namespace DMT\FileStream;

use CallbackFilterIterator;
use DMT\FileStream\Filter\CallbackFilter;
use DMT\FileStream\Filter\FilterInterface;
use DMT\FileStream\Modifier\CallbackModifier;
use DMT\FileStream\Modifier\ModifierInterface;
use DMT\FileStream\Reader\ObjectReaderInterface;
use Iterator;
use LimitIterator;

/**
 * @template T of object
 */
final class ReadStatement
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
     * @var list<ModifierInterface<T>>
     */
    private array $modifiers = [];

    /**
     * @param ObjectReaderInterface<T> $reader
     */
    public function __construct(
        private readonly ObjectReaderInterface $reader
    ) {
    }

    /**
     * Add a filter to the processor.
     *
     * @param FilterInterface<T>|callable(T, int): bool $filter
     */
    public function filter(FilterInterface|callable $filter): self
    {
        if (!$filter instanceof FilterInterface) {
            /** @var FilterInterface<T> $filter */
            $filter = new CallbackFilter($filter(...));
        }

        $this->filters[] = $filter;

        return $this;
    }

    /**
     * Set the offset and limit of the results.
     */
    public function limit(int $offset = 0, ?int $limit = null): self
    {
        $this->offset = $offset;
        $this->limit = $limit ?? -1;

        return $this;
    }

    /**
     * Add a modifier to the statement.
     *
     * @param ModifierInterface<T>|callable(T, int): T $modifier
     */
    public function modify(ModifierInterface|callable $modifier): self
    {
        if (!$modifier instanceof ModifierInterface) {
            /** @var ModifierInterface<T> $modifier */
            $modifier = new CallbackModifier($modifier(...));
        }

        $this->modifiers[] = $modifier;

        return $this;
    }

    /**
     * Get the results from the reader.
     *
     * Like execution a query on a database, the limit is applied after the filter(s).
     *
     * @return Iterator<int, T>
     */
    public function execute(): Iterator
    {
        $iterator = $this->reader->getResults();

        foreach ($this->filters as $filter) {
            $iterator = new CallbackFilterIterator($iterator, $filter);
        }

        $iterator = new LimitIterator($iterator, $this->offset, $this->limit);

        foreach ($iterator as $key => $object) {
            foreach ($this->modifiers as $modifier) {
                $object = $modifier->modify($object, $key);
            }

            yield $key => $object;
        }
    }
}
