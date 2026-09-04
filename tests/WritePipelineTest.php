<?php

declare(strict_types=1);

namespace DMT\Test\FileStream;

use DMT\FileStream\Reader\ObjectReaderInterface;
use DMT\FileStream\Transformer\TransformerInterface;
use DMT\FileStream\WritePipeline;
use DMT\FileStream\Writer\ObjectWriterInterface;
use Iterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WritePipeline::class)]
final class WritePipelineTest extends TestCase
{
    public function testWritesReaderObjectsWithoutTransformer(): void
    {
        $first = (object) ['id' => 1];
        $second = (object) ['id' => 2];

        $reader = $this->createMock(ObjectReaderInterface::class);
        $reader
            ->expects($this->once())
            ->method('getResults')
            ->willReturn(
                $this->iterator([
                    10 => $first,
                    20 => $second,
                ])
            );

        $writer = $this->createMock(ObjectWriterInterface::class);
        $writer
            ->expects($this->once())
            ->method('write')
            ->with(
                $this->callback(
                    function (iterable $objects) use ($first, $second): bool {
                        $this->assertSame(
                            [
                                10 => $first,
                                20 => $second,
                            ],
                            iterator_to_array($objects)
                        );

                        return true;
                    }
                )
            );

        $pipeline = new WritePipeline($writer);
        $pipeline->write($reader->getResults());
    }

    public function testTransformsObjectsBeforeWriting(): void
    {
        $reader = $this->createMock(ObjectReaderInterface::class);
        $reader
            ->method('getResults')
            ->willReturn(
                $this->iterator([
                    10 => (object) ['id' => 1],
                    20 => (object) ['id' => 2],
                ])
            );

        $transformer = $this->createMock(TransformerInterface::class);
        $transformer
            ->expects($this->exactly(2))
            ->method('transform')
            ->willReturnCallback(
                static fn (object $object): object => (object) [
                    'value' => $object->id * 10,
                ]
            );

        $writer = $this->createMock(ObjectWriterInterface::class);
        $writer
            ->expects($this->once())
            ->method('write')
            ->with(
                $this->callback(
                    function (iterable $objects): bool {
                        $objects = iterator_to_array($objects);

                        $this->assertSame([10, 20], array_keys($objects));
                        $this->assertSame(10, $objects[10]->value);
                        $this->assertSame(20, $objects[20]->value);

                        return true;
                    }
                )
            );

        $pipeline = new WritePipeline($writer);
        $pipeline->transform($transformer)->write($reader->getResults());
    }

    public function testPassesObjectsLazilyToWriter(): void
    {
        $reader = $this->createMock(ObjectReaderInterface::class);
        $reader
            ->method('getResults')
            ->willReturn(
                (function (): Iterator {
                    yield 0 => (object) ['id' => 1];

                    $this->fail(
                        'Reader should not be fully consumed before writer reads the iterable'
                    );
                })()
            );

        $writer = $this->createMock(ObjectWriterInterface::class);
        $writer
            ->expects($this->once())
            ->method('write')
            ->with(
                $this->callback(
                    function (iterable $objects): bool {
                        foreach ($objects as $object) {
                            $this->assertSame(1, $object->id);

                            break;
                        }

                        return true;
                    }
                )
            );

        $pipeline = new WritePipeline($writer);
        $pipeline->write($reader->getResults());
    }

    public function testTransformerIsLazy(): void
    {
        $reader = $this->createMock(ObjectReaderInterface::class);
        $reader
            ->method('getResults')
            ->willReturn(
                $this->iterator([
                    0 => (object) ['id' => 1],
                ])
            );

        $transformer = $this->createMock(TransformerInterface::class);
        $transformer
            ->expects($this->never())
            ->method('transform');

        $writer = $this->createMock(ObjectWriterInterface::class);
        $writer
            ->expects($this->once())
            ->method('write')
            ->with(
                $this->callback(
                    static fn (iterable $objects): bool => true
                )
            );

        $pipeline = new WritePipeline($writer);
        $pipeline->transform($transformer)->write($reader->getResults());
    }

    /**
     * @param array<int, object> $objects
     *
     * @return Iterator<int, object>
     */
    private function iterator(array $objects): Iterator
    {
        foreach ($objects as $key => $object) {
            yield $key => $object;
        }
    }
}
