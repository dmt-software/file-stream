<?php

declare(strict_types=1);

namespace DMT\FileStream\Writer\Stream;

use DMT\FileStream\Exception\WriterException;

interface StreamWriterInterface
{
    /**
     * Write data to the stream.
     *
     * @param string $data
     *
     * @throws WriterException WHen data could not be written to the stream.
     */
    public function write(string $data): void;
}
