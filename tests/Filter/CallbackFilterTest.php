<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Filter;

use ArrayObject;
use DMT\FileStream\Filter\CallbackFilter;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;
use stdClass;

#[CoversClass(CallbackFilter::class)]
final class CallbackFilterTest extends TestCase
{
    public function testReturnsCallbackResult(): void
    {
        $object = new stdClass();

        $acceptingFilter = new CallbackFilter(
            fn (object $value, int $key): bool => true
        );

        $rejectingFilter = new CallbackFilter(
            fn (object $value, int $key): bool => false
        );

        $this->assertTrue($acceptingFilter($object, 0));
        $this->assertFalse($rejectingFilter($object, 0));
    }

    public function testPassesObjectAndKeyToCallback(): void
    {
        $object = new stdClass();
        $receivedObject = null;
        $receivedKey = null;

        $filter = new CallbackFilter(
            function (object $value, int $key) use (&$receivedObject, &$receivedKey): bool {
                $receivedObject = $value;
                $receivedKey = $key;

                return true;
            }
        );

        $filter($object, 42);

        $this->assertSame($object, $receivedObject);
        $this->assertSame(42, $receivedKey);
    }

    public function testThrowsExceptionWhenObjectTypeDoesNotMatchCallback(): void
    {
        $filter = new CallbackFilter(
            fn (SimpleXMLElement $object): bool => true
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Callback not compatible with ObjectReader');

        $filter(new ArrayObject(), 0);
    }
}
