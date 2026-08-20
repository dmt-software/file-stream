<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ReaderException;
use Iterator;

interface ReaderInterface
{
    /**
     * Get the header part of the file stream.
     *
     * @throws NotFoundException
     * @throws ReaderException
     */
    public function getHeader(): object;

    /**
     * Get the results from the file stream.
     *
     * @return Iterator<object>
     * @throws NotFoundException
     * @throws ReaderException
     */
    public function getResults(): Iterator;
}
