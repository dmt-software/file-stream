<?php

declare(strict_types=1);

namespace DMT\FileStream;

interface SerializedWriterInterface
{
    /**
     * @param iterable<int, string> $values
     */
    public function write(iterable $values): void;
}
