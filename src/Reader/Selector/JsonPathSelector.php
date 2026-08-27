<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Selector;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ReaderException;
use DMT\FileStream\Reader\Parser\JsonObjectNode;
use DMT\FileStream\Reader\Parser\JsonObjectNodeParser;
use InvalidArgumentException;

/**
 * Selects a JSON object by a dotted path.
 *
 * Paths start at the unnamed JSON root and therefore must begin with "."
 *
 * Examples:
 * - "." selects objects at the root level.
 * - ".languages" selects objects below the "languages" property.
 * - ".response.data.languages" selects a nested path.
 * - ".response\.data.languages" treats "response.data" as a literal property name.
 *
 * @implements PathSelectorInterface<JsonObjectNode>
 */
final readonly class JsonPathSelector implements PathSelectorInterface
{
    /**
     * The default path to select the root object.
     */
    public const string ROOT_PATH = '.';

    public function __construct(
        private JsonObjectNodeParser $parser,
        private string $path = self::ROOT_PATH,
    ) {
        $this->validatePath();
    }

    /**
     * @inheritDoc
     */
    public function moveToNode(): JsonObjectNode
    {
        $segments = $this->parsePath();
        $stack = [];

        while ($node = $this->parser->parse()) {
            $stack = array_slice($stack, 0, $node->depth - 1);
            $stack[] = $node->name;

            if ($stack === $segments) {
                return $node;
            }
        }

        throw new NotFoundException('JSON path not found');
    }

    private function validatePath(): void
    {
        if ($this->path === self::ROOT_PATH) {
            return;
        }

        if (empty($this->path)) {
            throw new InvalidArgumentException('JSON path cannot be empty');
        }

        if ($this->isMalformedPath()) {
            throw new InvalidArgumentException('Malformed JSON path');
        }
    }

    private function isMalformedPath(): bool
    {
        return
            str_starts_with($this->path, '.')
            || str_ends_with($this->path, '.')
            || str_contains($this->path, '..')
        ;
    }

    /**
     * @return list<string|null>
     */
    private function parsePath(): array
    {
        if ($this->path === self::ROOT_PATH) {
            return [null];
        }

        $segments = preg_split('~(?<!\\\)\.~', substr($this->path, 1));

        if ($segments === false) {
            throw new ReaderException('Could not parse JSON path');
        }

        return [
            null,
            ...array_map(
                fn (string $segment): string => stripcslashes($segment),
                $segments,
            ),
        ];
    }
}
