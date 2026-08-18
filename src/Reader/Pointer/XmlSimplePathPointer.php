<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Pointer;

use DMT\FileStream\Exception\NotFoundException;
use DMT\XmlParser\Node\Element;
use DMT\XmlParser\Node\Text;
use DMT\XmlParser\Parser;

final class XmlSimplePathPointer implements PointerInterface
{
    /**
     * @var list<Element>
     */
    private array $stack = [];
    private string $path = '';

    public function __construct(private readonly Parser $parser)
    {
    }

    /**
     * @inheritDoc
     */
    public function setPointer(string $path): void
    {
        if ($path === $this->path) {
            return;
        }

        $this->path = $path;
        $paths = $this->getPaths($path);
        $depth = 0;
        $valid = '~' . str_replace('.', '[^/]+', $path) . '~';

        while ($node = $this->parser->parse()) {
            if ($node instanceof Text) {
                continue;
            }

            if ($depth >= $node->depth()) {
                array_pop($this->stack);
            }

            $depth = $node->depth() - 1;
            if ($depth <= count($paths)) {
                $this->stack[$depth] = $node;
            }

            if (count($paths) == count($this->stack) && preg_match($valid, $this->stackToPath($paths))) {
                break;
            }
        }

        if ($node === null) {
            throw new NotFoundException('End of file reached');
        }
    }

    /**
     * @return list<array{"namespace": ?string, "localName": string}>
     */
    private function getPaths(string $path): array
    {
        $callback = function (string $elem) {
            $matches = [];
            preg_match('~^(\{(?<namespace>[^}])+})?(?<localName>.*)$~', $elem, $matches, PREG_UNMATCHED_AS_NULL);

            return array_filter($matches, fn($key) => !is_int($key), ARRAY_FILTER_USE_KEY);
        };

        return array_map($callback, explode('/', $path));
    }

    /**
     * @param list<array{"namespace": ?string, "localName": string}> $paths
     */
    private function stackToPath(array $paths): string
    {
        $path = '';
        foreach ($this->stack as $key => $elem) {
            $path .= '/';
            if ($paths[$key]['namespace'] !== null) {
                $path .= '{' . $elem->namespace . '}';
            }
            $path .= $elem->localName;
        }

        return $path;
    }
}