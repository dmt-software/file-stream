<?php

declare(strict_types=1);

namespace DMT\FileStream\Format\Json;

use DMT\FileStream\Format\Json\Serialization\JsonEncodeSerializer;
use DMT\FileStream\Format\Json\Writer\JsonStreamWriter;
use DMT\FileStream\Format\Json\Writer\JsonTemplateParser;
use DMT\FileStream\Writer\ObjectWriterInterface;
use DMT\FileStream\Writer\StreamObjectWriter;
use InvalidArgumentException;
use pcrov\JsonReader\Exception;
use pcrov\JsonReader\JsonReader;
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
     * @param resource $output
     * @param resource|null $template
     */
    public function __construct(
        mixed $output,
        mixed $template = null,
        string $path = JsonPath::ROOT_PATH,
        int $flags = 0,
    ) {
        if (!is_resource($output)) {
            throw new InvalidArgumentException('Output must be a resource');
        }

        if (!is_null($template)  && !is_resource($template)) {
            throw new InvalidArgumentException('Template must be a resource');
        }

        try {
            if ($template) {
                $templateReader = new JsonReader($flags);
                $templateReader->stream($template);

                $template = new JsonTemplateParser($templateReader, new JsonPath($path), $output);
            }
        } catch (Exception $exception) {
            throw new InvalidArgumentException('Invalid template stream', previous: $exception);
        }

        $this->writer = new StreamObjectWriter(
            new JsonStreamWriter($output, $template),
            new JsonEncodeSerializer($flags),
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
