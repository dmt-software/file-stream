<?php

declare(strict_types=1);

namespace DMT\FileStream\Writer\Stream;

use DMT\FileStream\Csv\CsvControl;
use InvalidArgumentException;
use RuntimeException;

final readonly class CsvStreamWriter implements StreamWriterInterface
{
    /**
     * @param resource $stream
     */
    public function __construct(
        private mixed $stream,
        private CsvControl $control,
    ) {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Stream must be a resource');
        }
    }

    /**
     * @inheritDoc
     */
    public function write(string $data): void
    {
        $data .= $this->control->lineEnding;

        $written = fwrite($this->stream, $data);

        if ($written === false || $written !== strlen($data)) {
            throw new RuntimeException('Unable to write CSV record');
        }
    }
}
