<?php

declare(strict_types=1);

namespace DMT\FileStream\Filter;

use InvalidArgumentException;

/**
 * @template T of object
 */
interface FilterInterface
{
    /**
     * Apply the filter to the given object.
     *
     * @param T $object
     * @throws InvalidArgumentException When the filter does not support the given object.
     */
    public function __invoke(object $object, int $key): bool;
}
