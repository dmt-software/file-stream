<?php

declare(strict_types=1);

namespace DMT\FileStream\Exception;

use RuntimeException;

/**
 * Represents a failure while reading from a stream or reader.
 *
 * This exception is used when a read operation cannot be completed because
 * the underlying stream is unreadable, exhausted unexpectedly, cannot be
 * rewound, or otherwise fails during reading.
 */
class ReaderException extends RuntimeException implements Exception
{
    /**
     * Create a new unreadable exception.
     */
    public static function unreadable(): self
    {
        return new self('Stream cannot be read');
    }

    /**
     * Create a new cannot rewind exception.
     */
    public static function cannotRewind(): self
    {
        return new self('Stream can not rewind');
    }

    /**
     * Create a new empty exception.
     */
    public static function empty(): self
    {
        return new self('Stream appears to be empty');
    }
}
