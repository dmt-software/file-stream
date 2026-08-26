<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Filter;

use ArrayObject;
use DMT\FileStream\Filter\CallbackFilter;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;

final class CallbackFilterTest extends TestCase
{
    public function testReturnsTrueWhenCallbackAcceptsResult(): void
    {
        $filter = new CallbackFilter(
            fn (object $result, int $key): bool => true
        );

        $this->assertTrue(
            $filter(new stdClass(), 0)
        );
    }

    public function testReturnsFalseWhenCallbackRejectsResult(): void
    {
        $filter = new CallbackFilter(
            fn (object $result, int $key): bool => false
        );

        $this->assertFalse(
            $filter(new stdClass(), 0)
        );
    }

    public function testPassesResultAndKeyToCallback(): void
    {
        $result = new stdClass();

        $filter = new CallbackFilter(
            function (object $actualResult, int $key) use ($result): bool {
                $this->assertSame($result, $actualResult);
                $this->assertSame(42, $key);

                return true;
            }
        );

        $this->assertTrue(
            $filter($result, 42)
        );
    }

    public function testThrowsTypeErrorForUnsupportedResultType(): void
    {
        $filter = new CallbackFilter(
            fn (ArrayObject $result, int $key): bool => true
        );

        $this->expectException(TypeError::class);

        $filter(new stdClass(), 0);
    }
}
