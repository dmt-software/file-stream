<?php

declare(strict_types=1);

namespace DMT\FileStream\Stream;

/**
 * Represents a stream abstraction.
 *
 * A stream wraps an underlying stream implementation and exposes it through
 * a common contract used by readable and writable stream implementations.
 *
 * @template T of resource|object
 */
interface StreamInterface
{
    /**
     * Get the active stream implementation.
     *
     * The returned value represents the live stream implementation used by this
     * wrapper. Depending on the implementation, this may be the original PHP
     * stream resource or an object created around that resource, such as
     * XMLReader or XMLWriter.
     *
     * The returned value shares the same current state as the wrapper and may be
     * used for format-specific operations that are not exposed by the common
     * stream contract.
     *
     * @return T
     */
    public function getStream(): mixed;

    /**
     * Close the stream.
     *
     * This also closes any underlying resource stream.
     */
    public function close(): void;
}
