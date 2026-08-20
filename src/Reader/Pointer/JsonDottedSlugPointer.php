<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader\Pointer;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Exception\ReaderException;
use DMT\FileStream\Reader\Parser\JsonObjectParser;
use pcrov\JsonReader\Exception;
use pcrov\JsonReader\JsonReader;

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
    public function setPointer(bool $header = false): void
    {
        if ($header && $this->headerPath === null) {
            throw new NotFoundException('Header path not defined');
        }

        $path = $header ? $this->headerPath : $this->resultPath;

        if ($path === '') {
            return;
        }

        $paths = array_map(stripcslashes(...), preg_split('~(?<!\\\)\.~', $path));

        try {
            while ($node = $this->parser->parse()) {
                if ($node->depth > count($paths)) {
                    array_pop($this->stack);
                }

                if ($node->depth <= count($paths)) {
                    $this->stack[$node->depth] = $node->name ?? '';
                }

                if (count($this->stack) === count($paths) && implode('.', $this->stack) == $path) {
                    break;
                }
            }
        } catch (Exception $exception) {
            throw new ReaderException('Error while reading JSON', previous: $exception);
        }

        if ($node === null) {
            throw new NotFoundException('End of file reached');
        }
    }
}
