<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Transformer;

use ArrayObject;
use DMT\FileStream\Transformer\ToArrayObjectTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;
use stdClass;

#[CoversClass(ToArrayObjectTransformer::class)]
final class ToArrayObjectTransformerTest extends TestCase
{
    public function testTransformsStdClassToArrayObject(): void
    {
        $transformer = new ToArrayObjectTransformer();

        $object = new stdClass();
        $object->name = 'John';
        $object->age = 42;
        $object->active = true;
        $object->nullable = null;

        $result = $transformer->transform($object, 10);

        $this->assertInstanceOf(ArrayObject::class, $result);
        $this->assertSame(
            [
                'name' => 'John',
                'age' => 42,
                'active' => true,
                'nullable' => null,
            ],
            $result->getArrayCopy()
        );
    }

    public function testTransformsSimpleXmlElementToArrayObject(): void
    {
        $transformer = new ToArrayObjectTransformer();

        $object = new SimpleXMLElement(
            '<item><name>John</name><age>42</age></item>'
        );

        $result = $transformer->transform($object, 0);

        $this->assertSame(
            [
                'name' => 'John',
                'age' => '42',
            ],
            $result->getArrayCopy()
        );
    }

    public function testUnwrapsSingleValueArray(): void
    {
        $transformer = new ToArrayObjectTransformer();

        $object = new stdClass();
        $object->name = ['John'];

        $result = $transformer->transform($object, 0);

        $this->assertSame(
            ['name' => 'John'],
            $result->getArrayCopy()
        );
    }

    public function testKeepsListOfScalarValues(): void
    {
        $transformer = new ToArrayObjectTransformer();

        $object = new stdClass();
        $object->tags = ['one', 'two', null, 3];

        $result = $transformer->transform($object, 0);

        $this->assertSame(
            ['tags' => ['one', 'two', null, 3]],
            $result->getArrayCopy()
        );
    }

    public function testFiltersNonScalarValuesFromList(): void
    {
        $transformer = new ToArrayObjectTransformer();

        $object = new stdClass();
        $object->values = [
            'one',
            new stdClass(),
            'two',
            null,
        ];

        $result = $transformer->transform($object, 0);

        $this->assertSame(
            ['values' => ['one', 'two', null]],
            $result->getArrayCopy()
        );
    }

    public function testFlattensNestedObjectProperties(): void
    {
        $transformer = new ToArrayObjectTransformer();

        $address = new stdClass();
        $address->street = 'Main Street';
        $address->number = 10;

        $object = new stdClass();
        $object->name = 'John';
        $object->address = $address;

        $result = $transformer->transform($object, 0);

        $this->assertSame(
            [
                'name' => 'John',
                'address_street' => 'Main Street',
                'address_number' => 10,
            ],
            $result->getArrayCopy()
        );
    }

    public function testFlattensNestedAssociativeArray(): void
    {
        $transformer = new ToArrayObjectTransformer();

        $object = new stdClass();
        $object->address = [
            'street' => 'Main Street',
            'number' => 10,
        ];

        $result = $transformer->transform($object, 0);

        $this->assertSame(
            [
                'address_street' => 'Main Street',
                'address_number' => 10,
            ],
            $result->getArrayCopy()
        );
    }

    public function testFlattensNestedSimpleXmlElements(): void
    {
        $transformer = new ToArrayObjectTransformer();

        $object = new SimpleXMLElement(
            '<item><address><street>Main Street</street><number>10</number></address></item>'
        );

        $result = $transformer->transform($object, 0);

        $this->assertSame(
            [
                'address_street' => 'Main Street',
                'address_number' => '10',
            ],
            $result->getArrayCopy()
        );
    }

    public function testReturnsArrayObjectWithPropertyAccess(): void
    {
        $transformer = new ToArrayObjectTransformer();

        $object = new stdClass();
        $object->name = 'John';

        $result = $transformer->transform($object, 0);

        $this->assertSame('John', $result->name);
    }
}
