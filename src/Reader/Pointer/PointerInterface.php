<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Pointer;

use DMT\FileStream\Exception\NotFoundException;

interface PointerInterface
{
    /**
     * @throws NotFoundException
     */
    public function setPointer(bool $header = false): void;
}
