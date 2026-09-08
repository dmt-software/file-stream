<?php

declare(strict_types=1);

namespace DMT\FileStream\Format\Json\Reader;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Path\PathInterface;
use DMT\FileStream\Reader\PathSelectorInterface;

/**
 * Selects a JSON object by a dotted path.
 *
 * @implements PathSelectorInterface<JsonObjectNode>
 */
final class JsonObjectPathSelector implements PathSelectorInterface
{
    /**
     * The default path to select the root object.
     */
    public const string ROOT_PATH = '.';

    /**
     * @var list<JsonObjectNode>
     */
    private array $stack = [];

    public function __construct(
        private readonly JsonObjectNodeParser $parser,
        private readonly PathInterface $path,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function moveToNode(): JsonObjectNode
    {
        while ($node = $this->parser->parse()) {
            $this->stack = array_slice($this->stack, 0, $node->depth);
            $this->stack[] = $node->name;

            if ($this->path->matchesPath($this->stack)) {
                return $node;
            }
        }

        throw new NotFoundException('JSON path not found');
    }
}
