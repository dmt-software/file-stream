<?php

declare(strict_types=1);

namespace DMT\FileStream\Structured\Selector\Path;

use InvalidArgumentException;

/**
 * Represents a slash separated path (xpath like).
 *
 * Paths start at the document root.
 * A "." segment matches any element name.
 *
 *  Examples:
 *  - "/." selects the elements within the container root.
 *  - "/root/element" selects an exact path.
 *  - "/root/./element" matches "element" below any child of root.
 */
final readonly class SlashSeparatedPath implements PathInterface
{
    public const string ROOT_PATH = '/.';

    /**
     * @var list<string|null>
     */
    private array $segments;

    /**
     * The pattern used to match the path.
     */
    private string $pattern;

    public function __construct(private string $path = self::ROOT_PATH)
    {
        $this->validatePath();

        $this->segments = array_slice(explode('/', $path), 1);
        $this->pattern = '~^' . preg_replace('~(?<=/)\.~', '[^/]+', $path) . '$~';
    }

    public function getSegments(): array
    {
        return $this->segments;
    }

    public function matchesPath(array $segments): bool
    {
        if (count($segments) <> count($this->segments)) {
            return false;
        }

        return preg_match($this->pattern, implode('/', [null, ...$segments])) === 1;
    }

    private function validatePath(): void
    {
        if ($this->path == self::ROOT_PATH) {
            return;
        }

        if (empty($this->path)) {
            throw new InvalidArgumentException('Path cannot be empty');
        }

        if (!str_starts_with($this->path, '/')
            || str_ends_with($this->path, '/')
            || str_contains($this->path, '//')
        ) {
            throw new InvalidArgumentException('Malformed path');
        }
    }
}
