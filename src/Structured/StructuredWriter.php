<?php

declare(strict_types=1);

namespace DMT\FileStream\Structured;

use DMT\FileStream\Stream\WritableStreamInterface;
use DMT\FileStream\Structured\Template\TemplateHandlerInterface;
use DMT\FileStream\Writer\SerializedWriterInterface;

/**
 * Writes serialized values into a structured template.
 *
 * The template prefix is written before the values, and the remaining template
 * content is written afterwards. Once writing is complete, the output stream
 * is flushed and closed.
 */
final readonly class StructuredWriter implements SerializedWriterInterface
{
    public function __construct(
        private WritableStreamInterface $output,
        private TemplateHandlerInterface $template,
    ) {
    }

    /**
     * @param iterable<int, string> $values
     */
    public function write(iterable $values): void
    {
        $this->template->writePrefix($this->output);
        $this->output->flush();

        foreach ($values as $value) {
            $this->output->write($value);
        }

        $this->template->writeSuffix($this->output);
        $this->output->flush();
        $this->output->close();
    }
}
