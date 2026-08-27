<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader;

use Iterator;

/**
 * @template T of object
 */
interface ReaderInterface
{
    /**
     * Get the results of the reader.
     *
     * The key from the underlying iterator MUST be preserved so it can be
     * used to identify the position at which processing succeeded or failed.
     *
     * @return Iterator<int, T>
     */
    public function getResults(): Iterator;
}