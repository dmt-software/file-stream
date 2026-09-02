<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Selector;

use DMT\FileStream\Exception\NotFoundException;
use DMT\XmlParser\Node\Element;
use DMT\XmlParser\Parser;
use InvalidArgumentException;

/**
 * Selects an XML element using a simple absolute path.
 *
 * Paths start at the document root. A "." segment matches any element name.
 *
 * Examples:
 * - "/" selects the root element.
 * - "/root/element" selects an exact path.
 * - "/./element" matches "element" below any root element.
 *
 * @implements PathSelectorInterface<Element>
 */
final class XmlElementPathSelector implements PathSelectorInterface
{
    /**
     * The default path to select the root object.
     */
    public const string ROOT_PATH = '/';

    /**
     * @var list<Element>
     */
    private array $stack = [];

    public function __construct(
        private readonly Parser $parser,
        private readonly string $path = self::ROOT_PATH,
    ) {
        $this->validatePath();
    }

    public function moveToNode(): Element
    {
        $path = $this->path === self::ROOT_PATH ? '/.' : $this->path;
        $paths = $this->getPaths($path);
        $valid = '~^' . preg_replace('~(?<=/)\.~', '[^/]+', $path) . '$~';

        while ($node = $this->parser->parse()) {
            if (!$node instanceof Element) {
                continue;
            }

            $depth = $node->depth() - 1;

            $this->stack = array_slice($this->stack, 0, $depth);

            if ($depth < count($paths)) {
                $this->stack[$depth] = $node;
            }

            if (count($paths) == count($this->stack) && preg_match($valid, $this->stackToPath())) {
                return $node;
            }
        }

        throw new NotFoundException('End of file reached');
    }

    private function validatePath(): void
    {
        if ($this->path == self::ROOT_PATH) {
            return;
        }

        if (empty($this->path)) {
            throw new InvalidArgumentException('XML path cannot be empty');
        }

        if ($this->isMalformedPath()) {
            throw new InvalidArgumentException('Malformed XML path');
        }
    }

    private function isMalformedPath(): bool
    {
        return
            !str_starts_with($this->path, '/')
            || str_ends_with($this->path, '/')
            || str_contains($this->path, '//')
        ;
    }

    private function stackToPath(): string
    {
        $path = '';

        foreach ($this->stack as $elem) {
            $path .= '/' . $elem->localName;
        }

        return $path;
    }

    /**
     * @return list<string>
     */
    private function getPaths(string $path): array
    {
        return array_slice(explode('/', $path), 1);
    }
}