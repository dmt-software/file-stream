<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Stream\Buffer;

use DMT\FileStream\Stream\Buffer\OutputBuffer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(OutputBuffer::class)]
final class OutputBufferTest extends TestCase
{
    #[DataProvider('provideInvalidLimits')]
    public function testRejectInvalidBufferLimit(int $limit): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Buffer limit must be greater than zero');

        new OutputBuffer(fopen('php://memory', 'w+'), $limit);
    }

    public function testExposeBufferStream(): void
    {
        $destination = fopen('php://memory', 'w+');

        $buffer = new OutputBuffer($destination);

        $this->assertIsResource($buffer->getStream());
        $this->assertNotSame($destination, $buffer->getStream());
    }

    public function testKeepDataBufferedBelowLimit(): void
    {
        $destination = fopen('php://memory', 'w+');

        $buffer = new OutputBuffer($destination, 5);
        $buffer->write('test');

        $this->assertSame('', $this->getContents($destination));
    }

    public function testFlushWhenBufferLimitIsReached(): void
    {
        $destination = fopen('php://memory', 'w+');

        $buffer = new OutputBuffer($destination, 4);
        $buffer->write('test');

        $this->assertSame('test', $this->getContents($destination));
    }

    public function testFlushExistingBufferBeforeLimitIsExceeded(): void
    {
        $destination = fopen('php://memory', 'w+');

        $buffer = new OutputBuffer($destination, 4);

        $buffer->write('abc');
        $buffer->write('de');

        $this->assertSame('abc', $this->getContents($destination));

        $buffer->flush();

        $this->assertSame('abcde', $this->getContents($destination));
    }

    public function testFlushValueLargerThanBufferLimit(): void
    {
        $destination = fopen('php://memory', 'w+');

        $buffer = new OutputBuffer($destination, 4);
        $buffer->write('abcdef');

        $this->assertSame('abcdef', $this->getContents($destination));
    }

    public function testFlushBufferedData(): void
    {
        $destination = fopen('php://memory', 'w+');

        $buffer = new OutputBuffer($destination);
        $buffer->write('first');
        $buffer->write('second');
        $buffer->flush();

        $this->assertSame('firstsecond', $this->getContents($destination));
    }

    public function testReuseBufferAfterFlush(): void
    {
        $destination = fopen('php://memory', 'w+');

        $buffer = new OutputBuffer($destination);

        $buffer->write('first');
        $buffer->flush();

        $buffer->write('second');
        $buffer->flush();

        $this->assertSame('firstsecond', $this->getContents($destination));
    }

    public function testFlushEmptyBufferDoesNothing(): void
    {
        $destination = fopen('php://memory', 'w+');

        fwrite($destination, 'existing');

        $buffer = new OutputBuffer($destination);
        $buffer->flush();

        $this->assertSame('existing', $this->getContents($destination));
    }

    public function testCloseFlushesBufferedDataAndClosesBufferStream(): void
    {
        $destination = fopen('php://memory', 'w+');

        $buffer = new OutputBuffer($destination);
        $stream = $buffer->getStream();

        $buffer->write('test');
        $buffer->close();

        $this->assertSame('test', $this->getContents($destination));
        $this->assertFalse(is_resource($stream));
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function provideInvalidLimits(): iterable
    {
        yield 'zero' => [0];
        yield 'negative' => [-1];
    }

    /**
     * @param resource $stream
     */
    private function getContents(mixed $stream): string
    {
        rewind($stream);

        return stream_get_contents($stream);
    }
}
