<?php

declare(strict_types=1);

namespace DMT\FileStream\Selector;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ReaderException;

interface PathSelectorInterface
{
    /**
     * Move the stream forward until the given path is reached.
     *
     * @throws NotFoundException When the path cannot be found.
     * @throws ReaderException When the stream cannot be read.
     */
    public function moveTo(string $path): void;
}
