<?php

declare(strict_types=1);

namespace DMT\FileStream\Writer\Stream;

use DMT\FileStream\Writer\Parser\TemplateParserInterface;
use InvalidArgumentException;
use RuntimeException;

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
        private readonly mixed $stream,
        private readonly ?TemplateParserInterface $template = null
    ) {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Stream must be a resource');
        }
    }

    /**
     * @inheritDoc
     */
    public function prepare(): void
    {
        if ($this->template) {
            $this->template->copyToPlaceholder();

            return;
        }

        $this->writeToStream('[');
    }

    /**
     * @inheritDoc
     */
    public function write(string $data): void
    {
        if (!$this->first) {
            $this->writeToStream(',');
        }

        $this->writeToStream($data);

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

        $this->writeToStream(']');
    }

    private function writeToStream(string $data): void
    {
        $written = fwrite($this->stream, $data);

        if ($written === false || $written !== strlen($data)) {
            throw new RuntimeException('Unable to write complete JSON data');
        }
    }
}
