<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Filter;

use DMT\FileStream\Filter\HasPropertyFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(HasPropertyFilter::class)]
final class HasPropertyFilterTest extends TestCase
{
    public function testAcceptWhenPropertyExists(): void
    {
        $filter = new HasPropertyFilter('name');

        $object = new stdClass();
        $object->name = 'John';

        $this->assertTrue($filter->accept($object, 0));
    }

    public function testAcceptWhenPropertyExistsWithNullValue(): void
    {
        $filter = new HasPropertyFilter('name');

        $object = new stdClass();
        $object->name = null;

        $this->assertTrue($filter->accept($object, 0));
    }

    public function testRejectWhenPropertyDoesNotExist(): void
    {
        $filter = new HasPropertyFilter('name');

        $this->assertFalse($filter->accept(new stdClass(), 0));
    }
}
