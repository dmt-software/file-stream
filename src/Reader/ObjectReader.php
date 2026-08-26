<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader;

use DMT\FileStream\Reader\Pointer\PointerInterface;
use DMT\FileStream\Reader\Stream\StreamIterator;
use DMT\FileStream\Serialization\DeserializerInterface;
use Iterator;

/**
 * @template H of object
 * @template T of object
 * @implements ReaderInterface<H, T>
 */
final readonly class ObjectReader implements ReaderInterface
{
    /**
     * @param DeserializerInterface<string, T> $deserializer
     */
    public function __construct(
        private StreamIterator $reader,
        private PointerInterface $pointer,
        private DeserializerInterface $deserializer,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getHeader(): object
    {
        $this->pointer->moveToHeader();

        return $this->deserializer->deserialize(
            $this->reader->current()
        );
    }

    /**
     * @inheritDoc
     */
    public function getResults(): Iterator
    {
        $this->pointer->moveToResults();

        foreach ($this->reader as $key => $row) {
            yield $key => $this->deserializer->deserialize($row);
        }
    }
}
