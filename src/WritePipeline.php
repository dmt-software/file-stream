<?php

declare(strict_types=1);

namespace DMT\FileStream;

use DMT\FileStream\Reader\ObjectReaderInterface;
use DMT\FileStream\Transformer\TransformerInterface;
use DMT\FileStream\Writer\ObjectWriterInterface;

/**
 * @template T of object
 * @template R of object
 */
final class WritePipeline
{
    /**
     * @var TransformerInterface<T, R>|null
     */
    private ?TransformerInterface $transformer = null;

    /**
     * @param ObjectReaderInterface<T> $reader
     * @param ObjectWriterInterface<T|R> $writer
     */
    public function __construct(
        private readonly ObjectReaderInterface $reader,
        private readonly ObjectWriterInterface $writer,
    ) {
    }

    /**
     * @param TransformerInterface<T, R> $transformer
     */
    public function transform(TransformerInterface $transformer): void
    {
        $this->transformer = $transformer;
    }

    public function execute(): void
    {
        $objects = function () {
            foreach ($this->reader->getResults() as $key => $object) {
                yield $key => $this->transformer?->transform($object) ?? $object;
            };
        };

        $this->writer->write($objects());
    }
}
