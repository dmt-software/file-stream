<?php

namespace DMT\FileStream\Reader\Parser;

use pcrov\JsonReader\JsonReader;

class JsonObjectParser
{
    private int $depth = 0;
    private ?array $names = [];
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
                    $name = $this->reader->name() ?? end($this->names);
                    $this->depth++;

                    return $this->current = new JsonObjectNode($this->depth, $name);
                case JsonReader::END_OBJECT:
                    $this->depth--;
                    break;
                case JsonReader::ARRAY:
                    $this->names[] = $this->reader->name() ?? end($this->names);
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
        $this->current->value = $this->reader->value();

        return $this->current;
    }
}
