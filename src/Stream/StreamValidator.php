<?php

declare(strict_types=1);

namespace DMT\FileStream\Stream;

use InvalidArgumentException;

/**
 * Validates native PHP stream resources for use by file-stream components.
 *
 * The validator centralizes common stream capability checks so individual
 * reader and writer implementations do not need to duplicate resource and
 * mode validation.
 */
final class StreamValidator
{
    /**
     * Validate that a stream can be read.
     *
     * A readable stream must be opened in a mode that permits reading.
     *
     * @param resource $stream
     *
     * @throws InvalidArgumentException
     */
    public static function readable(mixed $stream): void
    {
        self::resource($stream);

        $mode = stream_get_meta_data($stream)['mode'] ?? '';

        if (strpbrk($mode, 'r+') === false) {
            throw new InvalidArgumentException('Stream is not readable');
        }
    }

    /**
     * Validate that a stream is suitable for writing a new document.
     *
     * The stream must be opened in a mode intended for fresh output.
     * Update mode (`r+`) is explicitly rejected because existing stream
     * contents must not be treated as part of the new document.
     *
     * @param resource $stream
     *
     * @throws InvalidArgumentException
     */
    public static function writable(mixed $stream): void
    {
        self::resource($stream);

        $mode = stream_get_meta_data($stream)['mode'] ?? '';

        if (strpbrk($mode, 'wx+') === false) {
            throw new InvalidArgumentException('Stream is not writable');
        }

        if (str_contains($mode, 'r+')) {
            throw new InvalidArgumentException(
                'Stream must not be opened in update mode'
            );
        }
    }

    /**
     * Validate that a value is a native PHP stream resource.
     *
     * @param resource $stream
     *
     * @throws InvalidArgumentException
     */
    public static function resource(mixed $stream): void
    {
        if (!is_resource($stream) || get_resource_type($stream) !== 'stream') {
            throw new InvalidArgumentException('Expected a stream resource');
        }
    }
}
