<?php

declare(strict_types=1);

namespace DMT\FileStream;

use CallbackFilterIterator;
use DMT\FileStream\Filter\CallbackFilter;
use DMT\FileStream\Filter\ExpressionFilter;
use DMT\FileStream\Filter\FilterInterface;
use DMT\FileStream\Reader\ObjectReaderInterface;
use Iterator;
use LimitIterator;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Configures filtering and pagination for object reader results.
 *
 * Filters are applied in the order they are added. Offset and limit are
 * applied after all filters, so the offset refers to accepted results rather
 * than to the original source positions.
 *
 * @template T of object
 */
final class ReadStatement
{
    /**
     * The filters to apply to the results.
     *
     * @var list<FilterInterface<T>>
     */
    private array $filters = [];

    /**
     * The offset to start reading from.
     */
    private int $offset = 0;

    /**
     * The maximum number of results to return.
     */
    private ?int $limit = null;

    /**
     * @param ObjectReaderInterface<T> $reader
     */
    public function __construct(
        private readonly ObjectReaderInterface $reader,
        private readonly ?ExpressionLanguage $expressionLanguage = null,
    ) {
    }

    /**
     * Add a filter to the results.
     *
     * @param FilterInterface<T>|callable(T, int): bool $filter
     */
    public function filter(FilterInterface|callable $filter): self
    {
        if (is_callable($filter)) {
            $filter = new CallbackFilter($filter(...));
        }

        $this->filters[] = $filter;

        return $this;
    }

    /**
     * Add a filter to the results based on an expression.
     */
    public function where(string $expression): self
    {
        return $this->filter(
            new ExpressionFilter($expression, $this->expressionLanguage)
        );
    }

    /**
     * Apply the offset and limit to the results.
     */
    public function limit(int $offset = 0, ?int $limit = null): self
    {
        $this->offset = $offset;
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return Iterator<int, T>
     */
    public function execute(): Iterator
    {
        $iterator = $this->reader->getResults();

        foreach ($this->filters as $filter) {
            $iterator = new CallbackFilterIterator($iterator, $filter->accept(...));
        }

        return new LimitIterator($iterator, $this->offset, $this->limit ?? -1);
    }
}
