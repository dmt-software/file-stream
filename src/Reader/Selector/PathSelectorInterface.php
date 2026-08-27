<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Selector;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ReaderException;

/**
 * @template Node of object
 */
interface PathSelectorInterface
{
    /**
     * Move the stream forward until the given path is reached.
     *
     * @return Node The first node matching the configured path.
     *
     * @throws NotFoundException When the path cannot be found.
     * @throws ReaderException When the stream cannot be read.
     */
    public function moveToNode(): object;
}
