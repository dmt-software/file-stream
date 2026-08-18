<?php

declare(strict_types=1);

namespace DMT\FileStream;

use DMT\FileStream\Reader\Pointer\PointerInterface;
use DMT\FileStream\Reader\Stream\StreamIterator;
use DMT\FileStream\Serialization\Deserializer;
use Iterator;

/**
 * @template T of object
 */
final readonly class Reader implements ReaderInterface
{
    public function __construct(
        private StreamIterator $reader,
        private PointerInterface $pointer,
        /** @var Deserializer<T> */
        private Deserializer $deserializer,
    ) {
    }

    /**
     * @return T
     */
    public function getHeader(mixed $query): object
    {
        $this->pointer->setPointer($query);

        return $this->deserializer->deserialize(
            $this->reader->current()
        );
    }

    /**
     * @return Iterator<T>
     */
    public function getResults(mixed $query): Iterator
    {
        $this->pointer->setPointer($query);

        foreach ($this->reader as $key => $row) {
            yield $key => $this->deserializer->deserialize($row);
        }
    }
}
