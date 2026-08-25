<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ReaderException;
use Iterator;

/**
 * @template T of object
 */
interface ReaderInterface
{
    /**
     * Get the header part of the file stream.
     *
     * @return T
     * @throws NotFoundException
     * @throws ReaderException
     */
    public function getHeader(): object;

    /**
     * Get the results from the file stream.
     *
     * @return Iterator<T>
     * @throws NotFoundException
     * @throws ReaderException
     */
    public function getResults(): Iterator;
}
