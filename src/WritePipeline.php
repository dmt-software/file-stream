<?php

declare(strict_types=1);

namespace DMT\FileStream;

use DMT\FileStream\Transformer\TransformerInterface;
use DMT\FileStream\Writer\ObjectWriterInterface;

/**
 * @template T of object
 * @template R of object
 */
final class WritePipeline implements ObjectWriterInterface
{
    /**
     * @var TransformerInterface<T, R>|null
     */
    private ?TransformerInterface $transformer = null;

    /**
     * @param ObjectWriterInterface<T|R> $writer
     */
    public function __construct(
        private readonly ObjectWriterInterface $writer,
    ) {
    }

    /**
     * @param TransformerInterface<T, R> $transformer
     */
    public function transform(TransformerInterface $transformer): self
    {
        $this->transformer = $transformer;

        return $this;
    }

    /**
     * @param iterable<int, T> $objects
     * @return void
     */
    public function write(iterable $objects): void
    {
        /** @var iterable<int, R|T> $transform */
        $transform = function () use ($objects): iterable {
            foreach ($objects as $key => $object) {
                yield $key => $this->transformer?->transform($object) ?? $object;
            };
        };

        $this->writer->write($transform());
    }
}
