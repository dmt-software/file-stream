<?php

declare(strict_types=1);

namespace DMT\FileStream\Format\Xml\Reader;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Path\PathInterface;
use DMT\FileStream\Reader\PathSelectorInterface;

/**
 * Selects an XML element using a simple absolute path.
 *
 * @implements PathSelectorInterface<XmlElementNode>
 */
final class XmlElementPathSelector implements PathSelectorInterface
{
    /**
     * The default path to select the root object.
     */
    public const string ROOT_PATH = '/.';

    /**
     * @var list<string>
     */
    private array $stack = [];

    public function __construct(
        private readonly XmlElementNodeParser $parser,
        private readonly PathInterface $path,
    ) {
    }

    public function moveToNode(): XmlElementNode
    {
        while ($node = $this->parser->parse()) {
            $this->stack = array_slice($this->stack, 0, $node->depth);
            $this->stack[$node->depth] = $node->name;

            if ($this->path->matchesPath($this->stack)) {
                return $node;
            }
        }

        throw new NotFoundException('End of file reached');
    }
}
