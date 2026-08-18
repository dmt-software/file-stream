<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Stream;

use DMT\FileStream\Exception\ReaderException;
use Iterator;

interface StreamIterator extends Iterator
{
    /**
     * @throws ReaderException
     */
    public function next(): void;

    /**
     * @throws ReaderException
     */
    public function current(): string;

    /**
     * @throws ReaderException
     */
    public function rewind(): void;
}
