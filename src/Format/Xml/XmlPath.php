<?php

declare(strict_types=1);

namespace DMT\FileStream\Format\Xml;

use DMT\FileStream\Path\PathInterface;
use DMT\XmlParser\Node\Element;
use InvalidArgumentException;

/**
 * Represents a validated XML path (xpath like).
 *
 * Paths start at the document root. A "." segment matches any element name.
 *
 *  Examples:
 *  - "/" selects the root element.
 *  - "/root/element" selects an exact path.
 *  - "/./element" matches "element" below any root element.
 */
final readonly class XmlPath implements PathInterface
{
    public const string ROOT_PATH = '/.';

    private array $segments;
    private string $pattern;

    public function __construct(
        private string $path = self::ROOT_PATH
    ) {
        $this->validatePath();

        $this->segments = array_slice(explode('/', $path), 1);
        $this->pattern = '~^' . preg_replace('~(?<=/)\.~', '[^/]+', $path) . '$~';
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
     * @param list<Element> $segments
     */
    public function matchesPath(array $segments): bool
    {
        if (count($segments) <> count($this->segments)) {
            return false;
        }

        $segments = array_map(
            static fn(Element $element): string => $element->localName,
            $segments
        );

        return preg_match($this->pattern, implode('/', [null, ...$segments])) === 1;
    }

    private function validatePath(): void
    {
        if ($this->path == self::ROOT_PATH) {
            return;
        }

        if (empty($this->path)) {
            throw new InvalidArgumentException('XML path cannot be empty');
        }

        if (!str_starts_with($this->path, '/')
            || str_ends_with($this->path, '/')
            || str_contains($this->path, '//')
        ) {
            throw new InvalidArgumentException('Malformed XML path');
        }
    }
}
