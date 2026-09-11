<?php

declare(strict_types=1);

namespace DMT\FileStream\Structured\Path;

use InvalidArgumentException;

/**
 * Represents a dotted path (aka dotted slug).
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
final readonly class DotSeparatedPath implements PathInterface
{
    public const string ROOT_PATH = '.';

    /**
     * @var list<string|null>
     */
    private array $segments;

    public function __construct(private string $path = self::ROOT_PATH)
    {
        $this->validatePath();
        $this->parsePath();
    }

    public function getSegments(): array
    {
        return $this->segments;
    }

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
            throw new InvalidArgumentException('Could not parse path');
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
            throw new InvalidArgumentException('Path cannot be empty');
        }

        if (!str_starts_with($this->path, '.')
            || str_ends_with($this->path, '.')
            || str_contains($this->path, '..')
        ) {
            throw new InvalidArgumentException('Malformed path');
        }
    }
}
