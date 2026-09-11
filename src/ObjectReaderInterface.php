<?php

declare(strict_types=1);

namespace DMT\FileStream;

use Iterator;

/**
 * Reads objects from an underlying data source.
 *
 * @template T of object
 */
interface ObjectReaderInterface
{
    /**
     * @return Iterator<int, T>
     */
    public function getResults(): Iterator;
}
