<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Reader\Stream;

use DMT\FileStream\Reader\Selector\XmlElementPathSelector;
use DMT\FileStream\Reader\Stream\XmlElementIterator;
use DMT\XmlParser\Parser;
use DMT\XmlParser\Source\FileParser;
use DMT\XmlParser\Source\StreamParser;
use DMT\XmlParser\Tokenizer\XmlReaderTokenizer;
use PHPUnit\Framework\TestCase;

final class XmlElementIteratorTest extends TestCase
{
    public function testIteratesSelectedElements(): void
    {
        $iterator = $this->iterator(
            path: '/root/group/element'
        );

        $results = array_values(
            iterator_to_array($iterator)
        );

        $this->assertCount(2, $results);
        $this->assertStringContainsString('<element id="1">One</element>', $results[0]);
        $this->assertStringContainsString('<element id="2">Two</element>', $results[1]);
    }

    public function testReturnsAllElementsMatchingDepthAndLocalName(): void
    {
        $iterator = $this->iterator(
            path: '/root/./element'
        );

        $results = array_values(
            iterator_to_array($iterator)
        );

        $this->assertCount(3, $results);
        $this->assertStringContainsString('<element id="1">One</element>', $results[0]);
        $this->assertStringContainsString('<element id="2">Two</element>', $results[1]);
        $this->assertStringContainsString('<element id="3">Three</element>', $results[2]);
    }

    public function testUsesSequentialKeys(): void
    {
        $iterator = $this->iterator(
            path: '/root/./element'
        );

        $this->assertSame(
            [0, 1, 2],
            array_keys(iterator_to_array($iterator))
        );
    }

    public function testRewindPositionsIteratorOnFirstElement(): void
    {
        $iterator = $this->iterator(
            path: '/root/group/element'
        );

        $iterator->rewind();

        $this->assertTrue($iterator->valid());
        $this->assertSame(0, $iterator->key());
        $this->assertStringContainsString('<element id="1">One</element>', $iterator->current());
    }

    public function testNextAdvancesToFollowingMatchingElement(): void
    {
        $iterator = $this->iterator(
            path: '/root/group/element'
        );

        $iterator->rewind();
        $iterator->current();
        $iterator->next();

        $this->assertTrue($iterator->valid());
        $this->assertSame(1, $iterator->key());
        $this->assertStringContainsString('<element id="2">Two</element>', $iterator->current());
    }

    public function testIteratorBecomesInvalidAtEndOfStream(): void
    {
        $iterator = $this->iterator(
            path: '/root/./element'
        );

        iterator_to_array($iterator);

        $this->assertFalse($iterator->valid());
    }

    public function testIteratorCannotRestartAfterItHasStarted(): void
    {
        $iterator = $this->iterator(
            path: '/root/group/element'
        );

        $this->assertCount(2, iterator_to_array($iterator));
        $this->assertSame([], iterator_to_array($iterator));
    }

    private function iterator(string $path): XmlElementIterator
    {
        $parser = $this->parser();

        return new XmlElementIterator(
            parser: $parser,
            selector: new XmlElementPathSelector(
                parser: $parser,
                path: $path
            )
        );
    }

    private function parser(): Parser
    {
        $stream = fopen(dirname(__DIR__, 2) . '/fixtures/xml/elements.xml', 'r');

        $this->assertIsResource($stream);

        return new Parser(new XmlReaderTokenizer(new StreamParser($stream)));
    }
}
