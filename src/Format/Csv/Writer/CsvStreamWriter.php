<?php

declare(strict_types=1);

namespace DMT\FileStream\Format\Csv\Writer;

use DMT\FileStream\Exception\WriterException;
use DMT\FileStream\Format\Csv\CsvControl;
use DMT\FileStream\Stream\WritableStreamInterface;
use DMT\FileStream\Writer\StreamWriterInterface;
use InvalidArgumentException;
use RuntimeException;

final readonly class CsvStreamWriter implements StreamWriterInterface
{
    /**
     * @param resource $stream
     */
    public function __construct(
        private WritableStreamInterface $stream,
        private CsvControl $control,
    ) {
        if (!$stream->isWritable()) {
            throw new InvalidArgumentException('Stream is not writable');
        }
    }

    /**
     * @inheritDoc
     */
    public function write(string $data): void
    {
        try {
            $this->stream->write($data . $this->control->lineEnding);
        } catch (WriterException) {
            throw new WriterException('Unable to write CSV record');
        }
    }
}
