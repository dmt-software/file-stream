<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Structured\Path;

use DMT\FileStream\Structured\Path\DotSeparatedPath;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(DotSeparatedPath::class)]
final class DotSeparatedPathTest extends TestCase
{
    public function testRootPathReturnsRootSegment(): void
    {
        $path = new DotSeparatedPath();

        $this->assertSame([null], $path->getSegments());
    }

    public function testParseSingleSegmentPath(): void
    {
        $path = new DotSeparatedPath('.languages');

        $this->assertSame([null, 'languages'], $path->getSegments());
    }

    public function testParseNestedPath(): void
    {
        $path = new DotSeparatedPath('.response.data.languages');

        $this->assertSame(
            [null, 'response', 'data', 'languages'],
            $path->getSegments()
        );
    }

    public function testEscapedDotIsPartOfSegmentName(): void
    {
        $path = new DotSeparatedPath('.response\.data.languages');

        $this->assertSame(
            [null, 'response.data', 'languages'],
            $path->getSegments()
        );
    }

    public function testMatchPath(): void
    {
        $path = new DotSeparatedPath('.response.data');

        $this->assertTrue($path->matchesPath([null, 'response', 'data']));
    }

    public function testDoesNotMatchDifferentPath(): void
    {
        $path = new DotSeparatedPath('.response.data');

        $this->assertFalse($path->matchesPath([null, 'response', 'items']));
    }

    public function testDoesNotMatchPartialPath(): void
    {
        $path = new DotSeparatedPath('.response.data');

        $this->assertFalse($path->matchesPath([null, 'response']));
    }

    #[DataProvider('provideMalformedPaths')]
    public function testRejectMalformedPath(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Malformed path');

        new DotSeparatedPath($value);
    }

    public function testRejectEmptyPath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Path cannot be empty');

        new DotSeparatedPath('');
    }

    public static function provideMalformedPaths(): iterable
    {
        return [
            'missing leading dot' => ['response.data'],
            'trailing dot' => ['.response.data.'],
            'double dot' => ['.response..data'],
        ];
    }
}
