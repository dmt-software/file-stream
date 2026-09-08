<?php

declare(strict_types=1);

namespace DMT\FileStream\Format\Xml\Reader;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Path\PathInterface;
use DMT\FileStream\Path\PathSelectorInterface;
use DMT\XmlParser\Node\Element;
use DMT\XmlParser\Parser;

/**
 * Selects an XML element using a simple absolute path.
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
        private readonly PathInterface $path,
    ) {
    }

    public function moveToNode(): Element
    {
        $paths = $this->path->getSegments();

        while ($node = $this->parser->parse()) {
            if (!$node instanceof Element) {
                continue;
            }

            $depth = $node->depth() - 1;

            $this->stack = array_slice($this->stack, 0, $depth);

            if ($depth < count($paths)) {
                $this->stack[$depth] = $node;
            }

            if ($this->path->matchesPath($this->stack)) {
                return $node;
            }
        }

        throw new NotFoundException('End of file reached');
    }
}
