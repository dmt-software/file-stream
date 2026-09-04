<?php

declare(strict_types=1);

namespace DMT\FileStream\Writer;

use DMT\FileStream\Serialization\JsonEncodeSerializer;
use DMT\FileStream\Writer\Parser\JsonTemplateParser;
use DMT\FileStream\Writer\Stream\JsonStreamWriter;
use InvalidArgumentException;
use stdClass;

/**
 * Writes stdClass instances as JSON.
 *
 * @implements ObjectWriterInterface<stdClass>
 */
final readonly class JsonObjectWriter implements ObjectWriterInterface
{
    private StreamObjectWriter $writer;

    /**
     * @param resource $stream
     * @param resource|null $template
     */
    public function __construct(
        mixed $stream,
        int $flags = 0,
        mixed $template = null,

    ) {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Stream must be a resource');
        }

        if (!is_null($template)  && !is_resource($template)) {
            throw new InvalidArgumentException('Template must be a resource');
        }

        $this->writer = new StreamObjectWriter(
            writer: new JsonStreamWriter(
                stream: $stream,
                template: $template ? new JsonTemplateParser($template, $stream) : null,
            ),
            serializer: new JsonEncodeSerializer(
                flags: $flags,
            ),
        );
    }

    /**
     * @param iterable<int, stdClass> $objects
     */
    public function write(iterable $objects): void
    {
        $this->writer->write($objects);
    }
}
