<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Parser;

use DMT\FileStream\Exception\ReaderException;
use pcrov\JsonReader\JsonReader;

final class JsonObjectParser
{
    private int $depth = 0;
    private array $names = [];
    private ?JsonObjectNode $current = null;

    public function __construct(private JsonReader $reader)
    {
    }

    public function parse(): ?JsonObjectNode
    {
        while ($this->reader->read()) {
            $type = $this->reader->type();

            switch ($type) {
                case JsonReader::OBJECT:
                    $name = $this->reader->name();
                    if (!$name && $this->names) {
                        $name = end($this->names);
                    }
                    $this->depth++;

                    return $this->current = new JsonObjectNode($this->depth, $name);
                case JsonReader::END_OBJECT:
                    $this->depth--;
                    break;
                case JsonReader::ARRAY:
                    $name = $this->reader->name() ?? '';
                    if (!$name && $this->names) {
                        $name = end($this->names);
                    }
                    $this->names[] = $name;
                    break;
                case JsonReader::END_ARRAY:
                    array_pop($this->names);
                default:
            }
        }

        return null;
    }

    public function parseValue(): JsonObjectNode
    {
        if ($this->current === null) {
            throw new ReaderException('No current JSON object');
        }

        $this->current->value = $this->reader->value();

        return $this->current;
    }
}
