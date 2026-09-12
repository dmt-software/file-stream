<?php

declare(strict_types=1);

namespace DMT\FileStream\Record\Boundary;

use DMT\FileStream\Config\CsvControlInterface;

/**
 * Detects logical CSV record boundaries.
 *
 * CSV records may span multiple physical lines when a field contains an
 * enclosed line break. This boundary checks whether the accumulated CSV data
 * represents a complete logical record.
 *
 * The complete current physical line must be included in the supplied data,
 * including its line ending when present. Passing only part of the current
 * line can produce an incorrect boundary result.
 */
final readonly class CsvRecordBoundary implements RecordBoundaryInterface
{
    /**
     * Expression to determine the current record enclosure is opened.
     */
    private string $opened;

    /**
     * Expression to determine the current record is enclosed.
     */
    private string $closed;

    /**
     * The line ending of the current physical line.
     */
    private string $lineEnding;

    /**
     * Construct the record boundary detector.
     */
    public function __construct(CsvControlInterface $control)
    {
        $escape = sprintf('(?<!%s)', preg_quote($control->escape ?: $control->enclosure, '~'));
        $delimiter = preg_quote($control->delimiter, '~');
        $enclosure = preg_quote($control->enclosure, '~');

        $this->opened = sprintf('~(?:^|%s)%s~', $delimiter, $enclosure);
        $this->closed = sprintf('~%s%s(?=%s|(?:\r?\n)?$)~', $escape, $enclosure, $delimiter);
        $this->lineEnding = $control->lineEnding;
    }

    /**
     * Check whether the buffered CSV data ends at a logical record boundary.
     *
     * The supplied data must include the complete current physical line.
     */
    public function isBoundary(string $data): bool
    {
        if (!str_ends_with($data, $this->lineEnding)) {
            return false;
        }

        $opened = 0;
        $closed = 0;

        preg_replace($this->opened, '$0', $data, count: $opened);
        preg_replace($this->closed, '$0', $data, count: $closed);

        return $opened == $closed;
    }
}
