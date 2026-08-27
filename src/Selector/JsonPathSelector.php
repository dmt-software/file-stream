<?php

declare(strict_types=1);

namespace DMT\FileStream\Selector;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ReaderException;
use DMT\FileStream\Reader\Parser\JsonObjectNodeParser;
use InvalidArgumentException;

/**
 * Selects a JSON object by a dotted path.
 *
 * Paths start at the unnamed JSON root and therefore must begin with ".".
 *
 * Examples:
 * - "." selects objects at the root level.
 * - ".languages" selects objects below the "languages" property.
 * - ".response.data.languages" selects a nested path.
 * - ".response\.data.languages" treats "response.data" as a literal property name.
 */
final readonly class JsonPathSelector implements PathSelectorInterface
{
    public function __construct(private JsonObjectNodeParser $parser)
    {
    }

    /**
     * @inheritDoc
     */
    public function moveTo(string $path): void
    {
        $this->validatePath($path);

        $segments = $this->parsePath($path);
        $stack = [];

        while ($node = $this->parser->parse()) {
            $stack = array_slice($stack, 0, $node->depth - 1);
            $stack[] = $node->name;

            if ($stack === $segments) {
                return;
            }
        }

        throw new NotFoundException('JSON path not found');
    }

    private function validatePath(string $path): void
    {
        if ($path === '.') {
            return;
        }

        if (!str_starts_with($path, '.') || str_ends_with($path, '.') || str_contains($path, '..')) {
            throw new InvalidArgumentException('Malformed JSON path');
        }
    }

    /**
     * @return list<string|null>
     */
    private function parsePath(string $path): array
    {
        if ($path === '.') {
            return [null];
        }

        $segments = preg_split('~(?<!\\\)\.~', substr($path, 1));

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
