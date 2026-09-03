<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Transformer;

use ArrayObject;
use DMT\FileStream\Transformer\ToJsonTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;
use stdClass;

#[CoversClass(ToJsonTransformer::class)]
final class ToJsonTransformerTest extends TestCase
{
    public function testTransformsArrayObjectToStdClass(): void
    {
        $object = new ArrayObject(
            [
                'name' => 'John',
                'age' => 42,
                'active' => true,
                'nullable' => null,
            ],
            ArrayObject::ARRAY_AS_PROPS
        );

        $result = (new ToJsonTransformer())->transform($object, 0);

        $this->assertInstanceOf(stdClass::class, $result);
        $this->assertSame('John', $result->name);
        $this->assertSame(42, $result->age);
        $this->assertTrue($result->active);
        $this->assertNull($result->nullable);
    }

    public function testTransformsNestedArrayObject(): void
    {
        $object = new ArrayObject(
            [
                'name' => 'John',
                'address' => new ArrayObject(
                    [
                        'street' => 'Main Street',
                        'number' => 10,
                    ],
                    ArrayObject::ARRAY_AS_PROPS
                ),
            ],
            ArrayObject::ARRAY_AS_PROPS
        );

        $result = (new ToJsonTransformer())->transform($object, 0);

        $this->assertInstanceOf(stdClass::class, $result->address);
        $this->assertSame('Main Street', $result->address->street);
        $this->assertSame(10, $result->address->number);
    }

    public function testTransformsSimpleXmlElementToStdClass(): void
    {
        $object = new SimpleXMLElement(
            '<user><name>John</name><age>42</age></user>'
        );

        $result = (new ToJsonTransformer())->transform($object, 0);

        $this->assertSame('John', $result->name);
        $this->assertSame('42', $result->age);
    }

    public function testTransformsNestedXmlElements(): void
    {
        $object = new SimpleXMLElement(
            '<user><address><street>Main Street</street><number>10</number></address></user>'
        );

        $result = (new ToJsonTransformer())->transform($object, 0);

        $this->assertInstanceOf(stdClass::class, $result->address);
        $this->assertSame('Main Street', $result->address->street);
        $this->assertSame('10', $result->address->number);
    }

    public function testCollectsRepeatedScalarXmlElementsIntoArray(): void
    {
        $object = new SimpleXMLElement(
            '<user><tag>one</tag><tag>two</tag><tag>three</tag></user>'
        );

        $result = (new ToJsonTransformer())->transform($object, 0);

        $this->assertCount(3, $result->tag);
        $this->assertSame(
            ['one', 'two', 'three'],
            $result->tag
        );
    }

    public function testCollectsRepeatedComplexXmlElementsIntoArray(): void
    {
        $object = new SimpleXMLElement(
            '<user>'
            . '<address><street>Main Street</street><number>10</number></address>'
            . '<address><street>Second Street</street><number>20</number></address>'
            . '</user>'
        );

        $result = (new ToJsonTransformer())->transform($object, 0);

        $this->assertCount(2, $result->address);

        $this->assertInstanceOf(stdClass::class, $result->address[0]);
        $this->assertSame('Main Street', $result->address[0]->street);
        $this->assertSame('10', $result->address[0]->number);

        $this->assertInstanceOf(stdClass::class, $result->address[1]);
        $this->assertSame('Second Street', $result->address[1]->street);
        $this->assertSame('20', $result->address[1]->number);
    }

    public function testTransformsArraysRecursively(): void
    {
        $object = new ArrayObject(
            [
                'values' => [
                    new ArrayObject(
                        ['name' => 'one'],
                        ArrayObject::ARRAY_AS_PROPS
                    ),
                    new ArrayObject(
                        ['name' => 'two'],
                        ArrayObject::ARRAY_AS_PROPS
                    ),
                ],
            ],
            ArrayObject::ARRAY_AS_PROPS
        );

        $result = (new ToJsonTransformer())->transform($object, 0);

        $this->assertCount(2, $result->values);
        $this->assertInstanceOf(stdClass::class, $result->values[0]);
        $this->assertInstanceOf(stdClass::class, $result->values[1]);
        $this->assertSame('one', $result->values[0]->name);
        $this->assertSame('two', $result->values[1]->name);
    }
}
