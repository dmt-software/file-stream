<?php

declare(strict_types=1);

namespace DMT\FileStream\Format\Json\Writer;

use DMT\FileStream\Stream\WritableStreamInterface;
use DMT\FileStream\Writer\FinalizeStreamInterface;
use DMT\FileStream\Writer\PrepareStreamInterface;
use DMT\FileStream\Writer\StreamWriterInterface;
use DMT\FileStream\Writer\TemplateParserInterface;
use InvalidArgumentException;

final class JsonStreamWriter implements
    StreamWriterInterface,
    PrepareStreamInterface,
    FinalizeStreamInterface
{
    private bool $first = true;

    /**
     * @param resource $stream
     */
    public function __construct(
        private readonly WritableStreamInterface $stream,
        private readonly ?TemplateParserInterface $template = null
    ) {
        if (!$stream->isWritable()) {
            throw new InvalidArgumentException('Stream is not writable');
        }
    }

    /**
     * @inheritDoc
     */
    public function prepare(): void
    {
        if ($this->template) {
            $this->template->copyToPath();

            return;
        }

        $this->stream->write('[');
    }

    /**
     * @inheritDoc
     */
    public function write(string $data): void
    {
        if (!$this->first) {
            $this->stream->write(',');
        }

        $this->stream->write($data);

        $this->first = false;
    }

    /**
     * @inheritDoc
     */
    public function finalize(): void
    {
        if ($this->template) {
            $this->template->copyRemainder();

            return;
        }

        $this->stream->write(']');
    }
}
