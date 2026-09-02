<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Reader\Stream;

use DMT\FileStream\Reader\Parser\JsonObjectNodeParser;
use DMT\FileStream\Reader\Selector\JsonObjectPathSelector;
use DMT\FileStream\Reader\Stream\JsonObjectIterator;
use pcrov\JsonReader\JsonReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonObjectIterator::class)]
final class JsonObjectIteratorTest extends TestCase
{
    public function testIteratesSelectedObjects(): void
    {
        $iterator = $this->iterator('objects.json', '.languages');

        $this->assertSame(
            [
                '{"name":"JavaScript","since":1995,"author":{"name":"Brendan Eich"}}',
                '{"name":"PHP","since":1995}',
            ],
            array_values(iterator_to_array($iterator))
        );
    }

    public function testReturnsAllObjectsMatchingSelectedDepthAndName(): void
    {
        $iterator = $this->iterator('global-objects.json', '.languages.language');

        $this->assertSame(
            [
                '{"name":"Javascript","since":1995,"by":"Brendan Eich"}',
                '{"name":"PHP","since":1995,"by":"Rasmus Lerdorf"}',
                '{"name":"C#","since":2000,"by":"Anders Hejlsberg"}',
            ],
            array_values(iterator_to_array($iterator))
        );
    }

    public function testIteratesObjectsFromRootArray(): void
    {
        $iterator = $this->iterator('root-array.json', '.');

        $this->assertSame(
            [
                '{"name":"Jane"}',
                '{"name":"John"}',
            ],
            array_values(iterator_to_array($iterator))
        );
    }

    public function testUsesSequentialKeys(): void
    {
        $iterator = $this->iterator('objects.json', '.languages');

        $this->assertSame(
            [0, 1],
            array_keys(iterator_to_array($iterator))
        );
    }

    private function iterator(string $fixture, string $path): JsonObjectIterator
    {
        $reader = new JsonReader();
        $reader->stream(fopen(dirname(__DIR__, 2) . '/fixtures/json/' . $fixture, 'r'));

        $parser = new JsonObjectNodeParser($reader);

        return new JsonObjectIterator($parser, new JsonObjectPathSelector($parser, $path));
    }
}
