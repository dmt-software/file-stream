<?php

declare(strict_types=1);

namespace DMT\FileStream;

use DMT\FileStream\Format\Json\JsonPath;
use DMT\FileStream\Format\Json\Serialization\JsonEncodeSerializer;
use DMT\FileStream\Format\Json\Writer\JsonStreamWriter;
use DMT\FileStream\Format\Json\Writer\JsonTemplateParser;
use DMT\FileStream\Path\PathInterface;
use DMT\FileStream\Stream\ReadableResourceStream;
use DMT\FileStream\Stream\ReadableStreamInterface;
use DMT\FileStream\Stream\WritableResourceStream;
use DMT\FileStream\Stream\WritableStreamInterface;
use DMT\FileStream\Writer\ObjectWriterInterface;
use DMT\FileStream\Writer\StreamObjectWriter;
use DMT\FileStream\Writer\TemplateParserInterface;
use InvalidArgumentException;
use pcrov\JsonReader\Exception;
use pcrov\JsonReader\JsonReader;
use stdClass;
use Throwable;

/**
 * Writes stdClass instances as JSON.
 *
 * @implements ObjectWriterInterface<stdClass>
 */
final class JsonObjectWriter implements ObjectWriterInterface
{
    private ?JsonTemplateParser $templateParser = null;

    /**
     * @param resource|WritableStreamInterface $stream
     * @param resource|ReadableStreamInterface|null $template
     */
    public function __construct(
        private mixed $stream {
            set => $value instanceof WritableStreamInterface ? $value : new WritableResourceStream($value);
        },
        mixed $template = null,
        string $path = JsonPath::ROOT_PATH,
        private readonly int $flags = 0,
    ) {
        if (is_null($template)) {
            return;
        }

        if (!$template instanceof ReadableStreamInterface) {
            $template = new ReadableResourceStream($template);
        }

        $this->setTemplateParser($template, new JsonPath($path));
    }

    /**
     * @param iterable<int, stdClass> $objects
     */
    public function write(iterable $objects): void
    {
        $writer = new StreamObjectWriter(
            new JsonStreamWriter($this->stream, $this->templateParser),
            new JsonEncodeSerializer($this->flags),
        );

        $writer->write($objects);
    }

    /**
     * @todo might be public
     */
    private function setTemplateParser(ReadableStreamInterface $template, PathInterface $path): void
    {
        if (!$template->isReadable()) {
            throw new InvalidArgumentException('Template is not readable');
        }

        try {
            $reader = new JsonReader();
            $reader->stream($template->getResource());
        } catch (Throwable $exception) {
            throw new InvalidArgumentException('Invalid template stream', previous: $exception);
        }

        $this->templateParser = new JsonTemplateParser($this->stream, $reader, $path);
    }
}
