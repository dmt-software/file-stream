<?php

declare(strict_types=1);

namespace DMT\FileStream\Format\Csv\Reader;

use DMT\FileStream\Format\Csv\CsvControl;
use DMT\FileStream\Stream\ReadableStreamInterface;
use InvalidArgumentException;

/**
 * Parses a CSV stream into complete records.
 *
 * Records may span multiple physical lines when enclosed fields contain line
 * endings. Parsing continues until the configured enclosures are balanced.
 */
final readonly class CsvLineParser
{
    /**
     * Regex pattern to determine the current record enclosure is opened.
     */
    private string $opened;

    /**
     * Regex pattern to determine the current record is enclosed.
     */
    private string $closed;

    public function __construct(private ReadableStreamInterface $stream, private CsvControl $control)
    {
        if (!$stream->isReadable()) {
            throw new InvalidArgumentException('Stream is not readable');
        }

        $escape = sprintf('(?<!%s)', preg_quote($control->escape ?: $control->enclosure, '~'));
        $delimiter = preg_quote($control->delimiter, '~');
        $enclosure = preg_quote($control->enclosure, '~');

        $this->opened = sprintf('~(?:^|%s)%s~', $delimiter, $enclosure);
        $this->closed = sprintf('~%s%s(?=%s|(?:\r?\n)?$)~', $escape, $enclosure, $delimiter);
    }

    public function parse(): ?string
    {
        $line = '';

        do {
            $line .= $this->stream->read(1);

            if (!str_ends_with($line, $this->control->lineEnding)) {
                continue;
            }

            if ($this->isFulfilledLine($line)) {
                return substr($line, 0, -strlen($this->control->lineEnding));
            }
        } while(!$this->stream->endOfFile());

        return $line !== '' ? $line : null;
    }

    /**
     * Check if the line is fully enclosed.
     */
    private function isFulfilledLine(string $line): bool
    {
        $opened = 0;
        $closed = 0;

        preg_replace($this->opened, '$0', $line, count: $opened);
        preg_replace($this->closed, '$0', $line, count: $closed);

        return $opened == $closed;
    }
}
