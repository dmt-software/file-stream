<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Csv;

use DMT\FileStream\Csv\CsvControl;
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

    /**
     * @param resource $stream
     */
    public function __construct(private mixed $stream, private CsvControl $control)
    {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Stream must be a resource');
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

        while (false !== ($char = fgetc($this->stream))) {
            $line .= $char;

            if (!str_ends_with($line, $this->control->lineEnding)) {
                continue;
            }

            if ($this->isFulfilledLine($line)) {
                return substr($line, 0, -strlen($this->control->lineEnding));
            }
        }

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
