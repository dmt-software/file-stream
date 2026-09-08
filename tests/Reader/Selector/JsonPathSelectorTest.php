<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Reader\Selector;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Format\Json\JsonPath;
use DMT\FileStream\Format\Json\Reader\JsonObjectNodeParser;
use DMT\FileStream\Format\Json\Reader\JsonObjectPathSelector;
use pcrov\JsonReader\JsonReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonObjectPathSelector::class)]
final class JsonPathSelectorTest extends TestCase
{
    public function testMovesToRootPath(): void
    {
        $selector = new JsonObjectPathSelector(
            parser: $this->parser('root-array.json'),
            path: new JsonPath('.')
        );

        $this->assertNull(
            $selector->moveToNode()->name
        );
    }

    public function testMovesToNestedPath(): void
    {
        $selector = new JsonObjectPathSelector(
            parser: $this->parser('objects.json'),
            path: new JsonPath('.meta')
        );

        $this->assertSame(
            'meta', $selector->moveToNode()->name
        );
    }

    public function testMovesToArrayPath(): void
    {
        $selector = new JsonObjectPathSelector(
            parser: $this->parser('objects.json'),
            path: new JsonPath('.languages')
        );

        $this->assertSame(
            'languages',
            $selector->moveToNode()->name
        );
    }

    public function testSupportsEscapedDotInPropertyName(): void
    {
        $selector = new JsonObjectPathSelector(
            parser: $this->parser('escaped-dot.json'),
            path: new JsonPath('.response\.data.languages')
        );

        $this->assertSame('languages', $selector->moveToNode()->name);
    }

    public function testThrowsWhenPathCannotBeFound(): void
    {
        $selector = new JsonObjectPathSelector(
            parser: $this->parser('objects.json'),
            path: new JsonPath('.missing')
        );

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessageIs('JSON path not found');

        $selector->moveToNode();
    }

    private function parser(string $fixture): JsonObjectNodeParser
    {
        $reader = new JsonReader();
        $reader->stream(fopen(dirname(__DIR__, 2) . '/fixtures/json/' . $fixture, 'r'));

        return new JsonObjectNodeParser($reader);
    }
}
