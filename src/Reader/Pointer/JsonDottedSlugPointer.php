<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Pointer;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ReaderException;
use DMT\FileStream\Reader\Parser\JsonObjectParser;
use pcrov\JsonReader\Exception;

final class JsonDottedSlugPointer implements PointerInterface
{
    private array $stack = [];

    public function __construct(
        private readonly JsonObjectParser $parser,
        private readonly string $resultPath,
        private readonly ?string $headerPath = null,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function moveToHeader(): void
    {
        if ($this->headerPath === null) {
            throw new ReaderException('Header path not defined');
        }

        $this->moveToPath($this->headerPath);
    }

    /**
     * @inheritDoc
     */
    public function moveToResults(): void
    {
        $this->moveToPath($this->resultPath);
    }

    private function moveToPath(string $path): void
    {
        if ($path === '') {
            return;
        }

        $paths = array_map(stripcslashes(...), preg_split('~(?<!\\\)\.~', $path));

        try {
            while ($node = $this->parser->parse()) {
                if ($node->depth > count($paths)) {
                    $this->stack = array_slice($this->stack, 0, $node->depth - 1);
                }

                if ($node->depth <= count($paths)) {
                    $this->stack[$node->depth - 1] = $node->name ?? '';
                }

                if ($this->stack === $paths) {
                    return;
                }
            }
        } catch (Exception $exception) {
            throw new ReaderException('Error while reading JSON', previous: $exception);
        }

        throw new NotFoundException('End of file reached');
    }
}
