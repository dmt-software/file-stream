<?php

declare(strict_types=1);

namespace DMT\FileStream\Writer;

use DMT\FileStream\Serialization\SerializerInterface;
use DMT\FileStream\Writer\Stream\FinalizeStreamInterface;
use DMT\FileStream\Writer\Stream\PrepareStreamInterface;
use DMT\FileStream\Writer\Stream\StreamWriterInterface;

/**
 * Writes serialized objects to a stream.
 *
 * @template T of object
 *
 * @implements ObjectWriterInterface<T>
 */
final readonly class StreamObjectWriter implements ObjectWriterInterface
{
    /**
     * @param SerializerInterface<T> $serializer
     */
    public function __construct(
        private StreamWriterInterface $writer,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function write(iterable $objects): void
    {
        if ($this->writer instanceof PrepareStreamInterface) {
            $this->writer->prepare();
        }

        foreach ($objects as $object) {
            $this->writer->write(
                $this->serializer->serialize($object)
            );
        }

        if ($this->writer instanceof FinalizeStreamInterface) {
            $this->writer->finalize();
        }
    }
}
