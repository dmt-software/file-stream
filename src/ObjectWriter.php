<?php

declare(strict_types=1);

namespace DMT\FileStream;

use DMT\FileStream\Serialization\SerializerInterface;

/**
 * Serializes objects and writes the resulting string values.
 *
 * @template T of object
 */
final readonly class ObjectWriter
{
    /**
     * @param SerializerInterface<T> $serializer
     */
    public function __construct(
        private SerializedWriterInterface $writer,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @param iterable<int, T> $objects
     */
    public function write(iterable $objects): void
    {
        $this->writer->write($this->serialize($objects));
    }

    /**
     * @param iterable<int, T> $objects
     * @return iterable<int, string>
     */
    private function serialize(iterable $objects): iterable
    {
        foreach ($objects as $key => $object) {
            yield $key => $this->serializer->serialize($object);
        }
    }
}
