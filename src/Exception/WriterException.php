<?php

declare(strict_types=1);

namespace DMT\FileStream\Exception;

use RuntimeException;

/**
 * Represents a failure while writing to a stream or writer.
 *
 * This exception is used when output cannot be written completely, the
 * underlying stream is not writable, or another write-side operation fails
 * while producing the serialized output.
 */
class WriterException extends RuntimeException implements Exception
{
    /**
     * Create a new unwritable exception.
     */
    public static function unwritable(): self
    {
        return new self('Stream cannot be written to');
    }

    /**
     * Create a new failure exception.
     */
    public static function failure(): self
    {
        return new self('Failed to write to stream');
    }
}
