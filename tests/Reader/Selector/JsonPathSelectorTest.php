<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Reader\Selector;

use DMT\FileStream\Exception\NotFoundException;
use DMT\FileStream\Reader\Parser\JsonObjectNodeParser;
use DMT\FileStream\Reader\Selector\JsonObjectPathSelector;
use InvalidArgumentException;
use pcrov\JsonReader\JsonReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonObjectPathSelector::class)]
final class JsonPathSelectorTest extends TestCase
{
    public function testMovesToRootPath(): void
    {
        $selector = new JsonObjectPathSelector(
            parser: $this->parser('root-array.json'),
            path: '.'
        );

        $this->assertNull(
            $selector->moveToNode()->name
        );
    }

    public function testMovesToNestedPath(): void
    {
        $selector = new JsonObjectPathSelector(
            parser: $this->parser('objects.json'),
            path: '.meta'
        );

        $this->assertSame(
            'meta', $selector->moveToNode()->name
        );
    }

    public function testMovesToArrayPath(): void
    {
        $selector = new JsonObjectPathSelector(
            parser: $this->parser('objects.json'),
            path: '.languages'
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
            path: '.response\.data.languages'
        );

        $this->assertSame('languages', $selector->moveToNode()->name);
    }

    #[DataProvider('malformedPathProvider')]
    public function testRejectsMalformedPath(string $path): void
    {
        $this->expectException(InvalidArgumentException::class);

        new JsonObjectPathSelector(
            parser: $this->parser('objects.json'),
            path: $path
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function malformedPathProvider(): iterable
    {
        yield 'missing leading dot' => ['meta'];
        yield 'trailing dot' => ['.meta.'];
        yield 'empty segment' => ['.meta..name'];
        yield 'empty path' => [''];
    }

    public function testThrowsWhenPathCannotBeFound(): void
    {
        $selector = new JsonObjectPathSelector(
            parser: $this->parser('objects.json'),
            path: '.missing'
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
