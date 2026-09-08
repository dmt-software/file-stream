<?php

declare(strict_types=1);

namespace DMT\FileStream\Format\Json\Writer;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ParserException;
use DMT\FileStream\Path\PathInterface;
use DMT\FileStream\Stream\WritableStreamInterface;
use DMT\FileStream\Writer\TemplateParserInterface;
use pcrov\JsonReader\Exception;
use pcrov\JsonReader\JsonReader;

final class JsonTemplateParser implements TemplateParserInterface
{
    private array $stack = [];
    private int $depth = -1;
    private bool $pathFound = false;

    public function __construct(
        private readonly WritableStreamInterface $stream,
        private readonly JsonReader $reader,
        private readonly PathInterface $path,
    ) {
    }

    public function copyToPath(): void
    {
        try {
            while ($this->reader->read()) {
                if ($this->path->matchesPath($this->stack)) {
                    $this->pathFound = true;
                    return;
                }

                $this->copyNode();
            }
        } catch (Exception $throwable) {
            throw new ParserException('Unable to parse JSON template', previous: $throwable);
        }

        throw new NotFoundException('Template path not found');
    }

    public function copyRemainder(): void
    {
        if (!$this->pathFound) {
            throw new ParserException('Copy remainder before path');
        }

        try {
            do {
                $this->copyNode();
            } while ($this->reader->read());
        } catch (Exception $throwable) {
            throw new ParserException('Unable to parse JSON template', previous: $throwable);
        }
    }

    private function copyNode(): void
    {
        $type = $this->reader->type();

        if (in_array($type, [JsonReader::END_ARRAY, JsonReader::END_OBJECT], true)) {
            $this->stack = array_slice($this->stack, 0, $this->reader->depth());
        } elseif ($this->reader->depth() === $this->depth) {
            $this->stream->write(',');
        }

        match ($this->reader->type()) {
            JsonReader::ARRAY => $this->copyArray(),
            JsonReader::END_ARRAY => $this->stream->write(']'),
            JsonReader::OBJECT => $this->copyObject(),
            JsonReader::END_OBJECT => $this->stream->write('}'),
            default => $this->copyValue(),
        };

        $this->depth = $this->reader->depth();
    }

    private function copyObject(): void
    {
        $name = $this->reader->name();
        $depth = $this->reader->depth();

        if ($name !== null || $depth == 0) {
            $this->stack[$depth] = $name;
        }

        $this->stream->write($name === null ? '{' : sprintf('%s:{', $this->encode($name)));
    }

    private function copyArray(): void
    {
        $name = $this->reader->name();
        $depth = $this->reader->depth();

        if ($name !== null || $depth == 0) {
            $this->stack[$depth] = $name;
        }

        $this->stream->write($name === null ? '[' : sprintf('%s:[', $this->encode($name)));
    }

    private function copyValue(): void
    {
        $name = $this->reader->name();
        $value = $this->encode($this->reader->value());

        if ($name !== null) {
            $value = sprintf('%s:%s', $this->encode($name), $value);
        }

        $this->stream->write($value);
    }

    private function encode(mixed $name): string
    {
        return json_encode($name) ?: '';
    }
}
