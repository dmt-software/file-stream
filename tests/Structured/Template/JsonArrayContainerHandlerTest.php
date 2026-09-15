<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Structured\Template;

use DMT\FileStream\Stream\JsonWriterStream;
use DMT\FileStream\Structured\Template\JsonArrayContainerHandler;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonArrayContainerHandler::class)]
final class JsonArrayContainerHandlerTest extends TestCase
{
    public function testWritePrefix(): void
    {
        $resource = fopen('php://memory', 'w+');
        $output = new JsonWriterStream($resource);

        $handler = new JsonArrayContainerHandler();
        $handler->writePrefix($output);

        rewind($resource);

        $this->assertSame('[', stream_get_contents($resource));
    }

    public function testWritePrefixAndSuffix(): void
    {
        $resource = fopen('php://memory', 'w+');
        $output = new JsonWriterStream($resource);

        $handler = new JsonArrayContainerHandler();
        $handler->writePrefix($output);
        $handler->writeSuffix($output);

        rewind($resource);

        $this->assertSame('[]', stream_get_contents($resource));
    }

    public function testWriteContainerAroundContent(): void
    {
        $resource = fopen('php://memory', 'w+');
        $output = new JsonWriterStream($resource);

        $handler = new JsonArrayContainerHandler();

        $handler->writePrefix($output);
        $output->write('{"name":"John"}');
        $handler->writeSuffix($output);

        rewind($resource);

        $this->assertSame(
            '[{"name":"John"}]',
            stream_get_contents($resource)
        );
    }

    public function testRejectPrefixWhenAlreadyWritten(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIs(
            'JSON template prefix was already written'
        );

        $resource = fopen('php://memory', 'w+');
        $output = new JsonWriterStream($resource);

        $handler = new JsonArrayContainerHandler();
        $handler->writePrefix($output);
        $handler->writePrefix($output);
    }

    public function testRejectSuffixBeforePrefix(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIs(
            'JSON template prefix was not written'
        );

        $output = new JsonWriterStream(fopen('php://memory', 'w+'));

        $handler = new JsonArrayContainerHandler();
        $handler->writeSuffix($output);
    }
}