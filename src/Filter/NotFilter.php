<?php

declare(strict_types=1);

namespace DMT\FileStream\Filter;

/**
 * Inverts the result of another filter.
 *
 * @template T of object
 * @implements FilterInterface<T>
 */
final readonly class NotFilter implements FilterInterface
{
    /**
     * @param FilterInterface<T> $filter
     */
    public function __construct(private FilterInterface $filter)
    {
    }

    public function accept(object $object, int $key): bool
    {
        return !$this->filter->accept($object, $key);
    }
}
