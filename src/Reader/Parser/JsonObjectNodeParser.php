<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Parser;

use DMT\FileStream\Exception\ParserException;
use JsonException;
use pcrov\JsonReader\Exception as JsonReaderException;
use pcrov\JsonReader\JsonReader;

final class JsonObjectNodeParser
{
    private int $depth = 0;
    private array $names = [];
    private ?JsonObjectNode $current = null;

    public function __construct(private readonly JsonReader $reader)
    {
    }

    /**
     * Parse the given JSON into object nodes by using the JsonReader.
     *
     * @throws ParserException
     */
    public function parse(): ?JsonObjectNode
    {
        try {
            while ($this->reader->read()) {
                $nodeType = $this->reader->type();

                switch ($nodeType) {
                    case JsonReader::OBJECT:
                        return $this->createObjectNode();

                    case JsonReader::END_OBJECT:
                        $this->leaveObject();
                        break;

                    case JsonReader::ARRAY:
                        $this->enterArray();
                        break;

                    case JsonReader::END_ARRAY:
                        $this->leaveArray();
                }
            }
        } catch (JsonReaderException $exception) {
            throw new ParserException('Error parsing JSON', previous: $exception);
        }

        return null;
    }

    /**
     * Apply the current value to the current object node.
     *
     * @throws ParserException
     */
    public function parseValue(): JsonObjectNode
    {
        try {
            $currentNode = $this->getCurrentNode();
            $currentNode->value = json_encode($this->reader->value(), JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new ParserException('Error reading JSON value', previous: $exception);
        }

        return $currentNode;
    }

    private function getCurrentNode(): JsonObjectNode
    {
        if ($this->current === null) {
            throw new ParserException('No current JSON object');
        }

        return $this->current;
    }

    private function createObjectNode(): JsonObjectNode
    {
        $this->depth++;

        $this->current = new JsonObjectNode(
            $this->depth,
            $this->getName()
        );

        return $this->current;
    }

    private function leaveObject(): void
    {
        $this->depth--;
    }

    private function enterArray(): void
    {
        $this->names[] = $this->getName();
    }

    private function leaveArray(): void
    {
        array_pop($this->names);
    }

    private function getName(): ?string
    {
        $name = $this->reader->name();

        if (!$name && $this->names) {
            $name = end($this->names);
        }

        return $name;
    }
}
