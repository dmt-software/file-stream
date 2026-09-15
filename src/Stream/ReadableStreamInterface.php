<?php

declare(strict_types=1);

namespace DMT\FileStream\Stream;

use DMT\FileStream\Exception\ReaderException;

/**
 * Represents a readable stream abstraction.
 *
 * The stream exposes a forward-moving cursor over an underlying stream
 * implementation. Consumers can advance the cursor, inspect the current
 * item and optionally rewind the stream when supported.
 *
 * @template T of resource|object
 * @extends StreamInterface<T>
 */
interface ReadableStreamInterface extends StreamInterface
{
    /**
     * Check whether the stream can currently be read.
     */
    public function isReadable(): bool;

    /**
     * Advance the stream cursor to the next item.
     *
     * The return value indicates whether the cursor was successfully moved
     * to a readable item.
     *
     * @throws ReaderException
     */
    public function next(): bool;

    /**
     * Get the contents of the current item.
     *
     * The returned value represents the item at the current cursor position
     * without advancing the stream.
     *
     * @throws ReaderException
     */
    public function current(): string;

    /**
     * Check whether the stream supports rewinding.
     */
    public function isRewindable(): bool;

    /**
     * Rewind the stream to its initial position.
     *
     * Implementations that cannot be rewound should throw a ReaderException.
     *
     * @throws ReaderException
     */
    public function rewind(): void;

    /**
     * Check whether the end of the stream has been reached.
     */
    public function eof(): bool;
}
