<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Pointer;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ReaderException;

interface PointerInterface
{
    /**
     * Move the pointer to the header section of the file stream.
     *
     * Header is expected to be found before the results section.
     *
     * @throws NotFoundException
     * @throws ReaderException
     */
    public function moveToHeader(): void;

    /**
     * @throws ReaderException
     */
    public function moveToResults(): void;
}
