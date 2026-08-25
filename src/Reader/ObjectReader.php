<?php

declare(strict_types=1);

namespace DMT\FileStream\Reader;

use DMT\FileStream\Reader\Pointer\PointerInterface;
use DMT\FileStream\Reader\Stream\StreamIterator;
use DMT\FileStream\Serialization\DeserializerInterface;
use Iterator;

/**
 * @template T of object
 */
final readonly class ObjectReader implements ReaderInterface
{
    public function __construct(
        private StreamIterator $reader,
        private PointerInterface $pointer,
        /** @var DeserializerInterface<T> */
        private DeserializerInterface $deserializer,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getHeader(): object
    {
        $this->pointer->advanceToHeader();

        return $this->deserializer->deserialize(
            $this->reader->current()
        );
    }

    /**
     * @inheritDoc
     */
    public function getResults(): Iterator
    {
        $this->pointer->advanceToResults();

        foreach ($this->reader as $key => $row) {
            yield $key => $this->deserializer->deserialize($row);
        }
    }
}
