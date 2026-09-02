<?php

declare(strict_types=1);

namespace DMT\FileStream\Writer;

use DMT\FileStream\Exception\SerializationException;
use DMT\FileStream\Exception\WriterException;

/**
 * @template T of object
 */
interface ObjectWriterInterface
{
    /**
     * Write all given objects to the configured stream.
     *
     * @param iterable<int, T> $objects
     *
     * @throws SerializationException When the object could not be serialized.
     * @throws WriterException When the object could not be written.
     */
    public function write(iterable $objects): void;
}
