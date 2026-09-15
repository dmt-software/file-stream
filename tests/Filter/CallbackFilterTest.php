<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Filter;

use ArrayObject;
use DMT\FileStream\Filter\CallbackFilter;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(CallbackFilter::class)]
final class CallbackFilterTest extends TestCase
{
    public function testAcceptUsingCallback(): void
    {
        $filter = new CallbackFilter(
            static fn (object $object, int $key): bool =>
                $object->active === true && $key === 2
        );

        $object = new stdClass();
        $object->active = true;

        $this->assertTrue($filter->accept($object, 2));
    }

    public function testRejectUsingCallback(): void
    {
        $filter = new CallbackFilter(
            static fn (object $object, int $key): bool =>
                $object->active === true && $key === 2
        );

        $object = new stdClass();
        $object->active = false;

        $this->assertFalse($filter->accept($object, 2));
    }

    public function testIncompatibleCallbackUsed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Callback not compatible with ObjectReader');
        
        $filter = new CallbackFilter(
            static fn (ArrayObject $object, int $key): bool =>
                $object->active === true
        );

        $object = new stdClass();
        $object->active = true;

        $filter->accept($object, 0);
    }
}
