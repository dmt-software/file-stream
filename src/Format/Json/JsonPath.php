<?php

declare(strict_types=1);

namespace DMT\FileStream\Format\Json;

use DMT\FileStream\Exception\ReaderException;
use DMT\FileStream\Path\PathInterface;
use InvalidArgumentException;

/**
 * Represents a validated JSON path (aka dotted slug).
 *
 * Paths start at the unnamed JSON root and therefore must begin with "."
 *
 *  Examples:
 *  - "." selects objects at the root level.
 *  - ".languages" selects objects below the "languages" property.
 *  - ".response.data.languages" selects a nested path.
 *  - ".response\.data.languages" treats "response.data" as a literal property name.
 *
 * The path is validated and split into ordered segments for use by
 * selectors and template parsers.
 */
final readonly class JsonPath implements PathInterface
{
    public const string ROOT_PATH = '.';

    /**
     * @var list<string|null>
     */
    private array $segments;

    public function __construct(
        private string $path = self::ROOT_PATH
    ) {
        $this->validatePath();
        $this->parsePath();
    }

    /**
     * @inheritDoc
     */
    public function getSegments(): array
    {
        return $this->segments;
    }

    /**
     * {@inheritDoc}
     *
     * @param list<string|null> $segments
     */
    public function matchesPath(array $segments): bool
    {
        return $this->segments === $segments;
    }

    private function parsePath(): void
    {
        if ($this->path === self::ROOT_PATH) {
            $this->segments = [null];
            return;
        }

        $segments = preg_split('~(?<!\\\)\.~', substr($this->path, 1));

        if ($segments === false) {
            throw new ReaderException('Could not parse JSON path');
        }

        $segments = array_map(
            fn (string $segment): string => stripcslashes($segment),
            $segments,
        );

        $this->segments = [null, ...$segments];
    }

    private function validatePath(): void
    {
        if ($this->path === self::ROOT_PATH) {
            return;
        }

        if (empty($this->path)) {
            throw new InvalidArgumentException('JSON path cannot be empty');
        }

        if (!str_starts_with($this->path, '.')
            || str_ends_with($this->path, '.')
            || str_contains($this->path, '..')
        ) {
            throw new InvalidArgumentException('Malformed JSON path');
        }
    }
}
