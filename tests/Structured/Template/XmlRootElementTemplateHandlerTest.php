<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Structured\Template;

use DMT\FileStream\Stream\ResourceWriterStream;
use DMT\FileStream\Structured\Template\XmlRootElementTemplateHandler;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(XmlRootElementTemplateHandler::class)]
final class XmlRootElementTemplateHandlerTest extends TestCase
{
    public function testWriteDefaultRootElement(): void
    {
        $resource = fopen('php://memory', 'w+');
        $output = new ResourceWriterStream($resource);

        $handler = new XmlRootElementTemplateHandler();
        $handler->writePrefix($output);
        $handler->writeSuffix($output);

        rewind($resource);

        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8"?><Results></Results>',
            stream_get_contents($resource)
        );
    }

    public function testWriteCustomRootElement(): void
    {
        $resource = fopen('php://memory', 'w+');
        $output = new ResourceWriterStream($resource);

        $handler = new XmlRootElementTemplateHandler('Items');
        $handler->writePrefix($output);
        $handler->writeSuffix($output);

        rewind($resource);

        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8"?><Items></Items>',
            stream_get_contents($resource)
        );
    }

    public function testWriteTemplateAroundContent(): void
    {
        $resource = fopen('php://memory', 'w+');
        $output = new ResourceWriterStream($resource);

        $handler = new XmlRootElementTemplateHandler('Items');
        $handler->writePrefix($output);
        $output->write('<Item>one</Item>');
        $handler->writeSuffix($output);

        rewind($resource);

        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8"?><Items><Item>one</Item></Items>',
            stream_get_contents($resource)
        );
    }

    public function testRejectPrefixWhenAlreadyWritten(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIs(
            'XML template prefix was already written'
        );

        $resource = fopen('php://memory', 'w+');
        $output = new ResourceWriterStream($resource);

        $handler = new XmlRootElementTemplateHandler();
        $handler->writePrefix($output);
        $handler->writePrefix($output);
    }

    public function testRejectSuffixBeforePrefix(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIs(
            'XML template prefix was not written'
        );

        $resource = fopen('php://memory', 'w+');
        $output = new ResourceWriterStream($resource);

        $handler = new XmlRootElementTemplateHandler();
        $handler->writeSuffix($output);
    }
}
