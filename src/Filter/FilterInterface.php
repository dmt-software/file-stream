<?php

declare(strict_types=1);

namespace DMT\FileStream\Filter;

use InvalidArgumentException;

/**
 * Determines whether objects should be accepted for further processing.
 *
 * Implementations may inspect both the object and its source key to decide
 * whether the object should be included.
 *
 * @template T of object
 */
interface FilterInterface
{
    /**
     * @param T $object
     *
     * @throws InvalidArgumentException
     */
    public function accept(object $object, int $key): bool;
}
