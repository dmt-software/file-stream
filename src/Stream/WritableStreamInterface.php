<?php

declare(strict_types=1);

namespace DMT\FileStream\Stream;

use DMT\FileStream\Exception\WriterException;

/**
 * Represents a writable stream abstraction.
 *
 * The stream exposes an underlying writable stream implementation and allows
 * data to be written sequentially to its current output position.
 *
 * Implementations are intentionally forward-only. Rewinding or resetting the
 * output is not part of this contract.
 *
 * @template T of resource|object
 * @extends StreamInterface<T>
 */
interface WritableStreamInterface extends StreamInterface
{
    /**
     * Check whether the stream can currently be written to.
     */
    public function isWritable(): bool;

    /**
     * Write data to the current output position.
     *
     * Implementations should write the complete value or throw when the
     * operation cannot be completed.
     *
     * @throws WriterException
     */
    public function write(string $data): void;

    /**
     * Flush buffered output to the underlying stream when applicable.
     *
     * Implementations without buffering may treat this as a no-op.
     *
     * @throws WriterException
     */
    public function flush(): void;
}
