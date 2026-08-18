<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Pointer;

class NullPointer implements PointerInterface
{
    /**
     * @inheritDoc
     */
    public function setPointer(string $path): void
    {
        // keep the file pointer where it is.
    }
}