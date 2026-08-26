<?php

declare(strict_types=1);

namespace DMT\FileStream\Filter;

/**
 * @template T of object
 */
interface FilterInterface
{
    /**
     * Call the filter on the given result.
     *
     * @param T $result
     */
    public function __invoke(object $result, int $key): bool;
}
