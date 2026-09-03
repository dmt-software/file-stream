<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Writer\Stream;

use DMT\FileStream\Writer\Parser\TemplateParserInterface;
use DMT\FileStream\Writer\Stream\JsonStreamWriter;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(JsonStreamWriter::class)]
final class JsonStreamWriterTest extends TestCase
{
    public function testWritesJsonArray(): void
    {
        $stream = fopen('php://temp', 'r+');

        $writer = new JsonStreamWriter($stream);
        $writer->prepare();
        $writer->write('{"id":1}');
        $writer->write('{"id":2}');
        $writer->finalize();

        rewind($stream);

        $this->assertSame(
            '[{"id":1},{"id":2}]',
            stream_get_contents($stream)
        );
    }

    public function testWritesEmptyJsonArray(): void
    {
        $stream = fopen('php://temp', 'r+');

        $writer = new JsonStreamWriter($stream);
        $writer->prepare();
        $writer->finalize();

        rewind($stream);

        $this->assertSame('[]', stream_get_contents($stream));
    }

    public function testDoesNotPrependSeparatorToFirstItem(): void
    {
        $stream = fopen('php://temp', 'r+');

        $writer = new JsonStreamWriter($stream);
        $writer->prepare();
        $writer->write('{"id":1}');

        rewind($stream);

        $this->assertSame('[{"id":1}', stream_get_contents($stream));
    }

    public function testPrependsSeparatorToFollowingItems(): void
    {
        $stream = fopen('php://temp', 'r+');

        $writer = new JsonStreamWriter($stream);
        $writer->prepare();
        $writer->write('{"id":1}');
        $writer->write('{"id":2}');

        rewind($stream);

        $this->assertSame('[{"id":1},{"id":2}', stream_get_contents($stream));
    }

    public function testUsesTemplateParserForPrepareAndFinalize(): void
    {
        $stream = fopen('php://temp', 'r+');

        $template = $this->createMock(TemplateParserInterface::class);
        $template
            ->expects($this->once())
            ->method('copyToPlaceholder');

        $template
            ->expects($this->once())
            ->method('copyRemainder');

        $writer = new JsonStreamWriter(
            stream: $stream,
            template: $template
        );
        $writer->prepare();
        $writer->write('{"id":1}');
        $writer->finalize();

        rewind($stream);

        $this->assertSame('{"id":1}', stream_get_contents($stream));
    }

    public function testWritesUtf8Data(): void
    {
        $stream = fopen('php://temp', 'r+');

        $writer = new JsonStreamWriter($stream);
        $writer->prepare();
        $writer->write('{"name":"René"}');
        $writer->finalize();

        rewind($stream);

        $this->assertSame('[{"name":"René"}]', stream_get_contents($stream));
    }

    public function testRejectsInvalidStream(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Stream must be a resource');

        new JsonStreamWriter(null);
    }

    public function testThrowsWhenStreamCannotBeWritten(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Unable to write complete JSON data');

        $writer = new JsonStreamWriter(fopen('php://memory', 'r'));
        @$writer->prepare();
    }
}
