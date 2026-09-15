<?php

declare(strict_types=1);

namespace DMT\FileStream\Writer;

/**
 * @template T of object
 */
interface ObjectWriterInterface
{
    /**
     * @param iterable<int, T> $objects
     */
    public function write(iterable $objects): void;
}