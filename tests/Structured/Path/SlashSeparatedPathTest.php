<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Structured\Path;

use DMT\FileStream\Structured\Path\SlashSeparatedPath;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SlashSeparatedPath::class)]
final class SlashSeparatedPathTest extends TestCase
{
    public function testRootPathReturnsWildcardSegment(): void
    {
        $path = new SlashSeparatedPath();

        $this->assertSame(['.'], $path->getSegments());
    }

    public function testParseExactPath(): void
    {
        $path = new SlashSeparatedPath('/root/element');

        $this->assertSame(['root', 'element'], $path->getSegments());
    }

    public function testMatchExactPath(): void
    {
        $path = new SlashSeparatedPath('/root/element');

        $this->assertTrue($path->matchesPath(['root', 'element']));
    }

    public function testDoesNotMatchDifferentExactPath(): void
    {
        $path = new SlashSeparatedPath('/root/element');

        $this->assertFalse($path->matchesPath(['root', 'other']));
    }

    public function testWildcardMatchesAnySingleSegment(): void
    {
        $path = new SlashSeparatedPath('/root/./element');

        $this->assertTrue($path->matchesPath(['root', 'child', 'element']));
        $this->assertTrue($path->matchesPath(['root', 'other', 'element']));
    }

    public function testWildcardDoesNotMatchMissingSegment(): void
    {
        $path = new SlashSeparatedPath('/root/./element');

        $this->assertFalse($path->matchesPath(['root', 'element']));
    }

    public function testWildcardDoesNotMatchAdditionalSegment(): void
    {
        $path = new SlashSeparatedPath('/root/./element');

        $this->assertFalse(
            $path->matchesPath(['root', 'child', 'nested', 'element'])
        );
    }

    public function testRootWildcardMatchesSingleRootElement(): void
    {
        $path = new SlashSeparatedPath('/.');

        $this->assertTrue($path->matchesPath(['root']));
    }

    public function testRootWildcardDoesNotMatchMultipleSegments(): void
    {
        $path = new SlashSeparatedPath('/.');

        $this->assertFalse($path->matchesPath(['root', 'element']));
    }

    #[DataProvider('provideMalformedPaths')]
    public function testRejectMalformedPath(string $path): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Malformed path');

        new SlashSeparatedPath($path);
    }

    public function testRejectEmptyPath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Path cannot be empty');

        new SlashSeparatedPath('');
    }

    public static function provideMalformedPaths(): iterable
    {
        return [
            'missing leading slash' => ['root/element'],
            'trailing slash' => ['/root/element/'],
            'double slash' => ['/root//element'],
        ];
    }
}
