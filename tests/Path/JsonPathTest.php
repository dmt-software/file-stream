<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Path;

use DMT\FileStream\Format\Json\JsonPath;
use InvalidArgumentException as InvalidArgumentExceptionAlias;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonPath::class)]
class JsonPathTest extends TestCase
{
    public function testMatchesPath(): void
    {
        $this->assertTrue(new JsonPath('.result.data')->matchesPath([null, 'result', 'data']));
    }

    public function testDoesNotMatchPath(): void
    {
        $this->assertFalse(new JsonPath('.result.data')->matchesPath([null, 'result', 'meta']));
    }

    #[DataProvider('pathProvider')]
    public function testGetSegments(string $path, array $expected): void
    {
        $this->assertSame($expected, new JsonPath($path)->getSegments());
    }

    /**
     * @return iterable<string, array{string, array<int, string|null>}>
     */
    public static function pathProvider(): iterable
    {
        yield 'single segment' => ['.meta.name', [null, 'meta', 'name']];
        yield 'multiple segments' => ['.meta.name.first', [null, 'meta', 'name', 'first']];
        yield 'dot in key' => ['.response\.data.languages', [null, 'response.data', 'languages']];
    }

    #[DataProvider('malformedPathProvider')]
    public function testThrowsExceptionOnMalformedPath(string $path): void
    {
        $this->expectException(InvalidArgumentExceptionAlias::class);

        new JsonPath($path);
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
}
