<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Transformer;

use ArrayObject;
use DMT\FileStream\Transformer\ToXmlTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;
use stdClass;

#[CoversClass(ToXmlTransformer::class)]
final class ToXmlTransformerTest extends TestCase
{
    public function testTransformsArrayObjectToSimpleXmlElement(): void
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

        $result = (new ToXmlTransformer())->transform($object, 0);

        $this->assertInstanceOf(SimpleXMLElement::class, $result);
        $this->assertSame('result', $result->getName());
        $this->assertSame('John', (string) $result->name);
        $this->assertSame('42', (string) $result->age);
        $this->assertSame('1', (string) $result->active);
        $this->assertSame('', (string) $result->nullable);
    }

    public function testTransformsStdClassToSimpleXmlElement(): void
    {
        $object = new stdClass();
        $object->name = 'John';
        $object->age = 42;

        $result = (new ToXmlTransformer())->transform($object, 0);

        $this->assertSame('John', (string) $result->name);
        $this->assertSame('42', (string) $result->age);
    }

    public function testUsesConfiguredRootElement(): void
    {
        $object = new ArrayObject(
            ['name' => 'John'],
            ArrayObject::ARRAY_AS_PROPS
        );

        $result = (new ToXmlTransformer('user'))->transform($object, 0);

        $this->assertSame('user', $result->getName());
    }

    public function testTransformsNestedObject(): void
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

        $result = (new ToXmlTransformer())->transform($object, 0);

        $this->assertSame('Main Street', (string) $result->address->street);
        $this->assertSame('10', (string) $result->address->number);
    }

    public function testTransformsNestedStdClass(): void
    {
        $address = new stdClass();
        $address->street = 'Main Street';
        $address->number = 10;

        $object = new stdClass();
        $object->address = $address;

        $result = (new ToXmlTransformer())->transform($object, 0);

        $this->assertSame('Main Street', (string) $result->address->street);
        $this->assertSame('10', (string) $result->address->number);
    }

    public function testTransformsArrayValuesToRepeatedElements(): void
    {
        $object = new ArrayObject(
            [
                'tag' => [
                    'one',
                    'two',
                    'three',
                ],
            ],
            ArrayObject::ARRAY_AS_PROPS
        );

        $result = (new ToXmlTransformer())->transform($object, 0);

        $this->assertCount(3, $result->tag);
        $this->assertSame('one', (string) $result->tag[0]);
        $this->assertSame('two', (string) $result->tag[1]);
        $this->assertSame('three', (string) $result->tag[2]);
    }

    public function testTransformsArrayOfComplexValuesToRepeatedElements(): void
    {
        $object = new ArrayObject(
            [
                'address' => [
                    new ArrayObject(
                        [
                            'street' => 'Main Street',
                            'number' => 10,
                        ],
                        ArrayObject::ARRAY_AS_PROPS
                    ),
                    new ArrayObject(
                        [
                            'street' => 'Second Street',
                            'number' => 20,
                        ],
                        ArrayObject::ARRAY_AS_PROPS
                    ),
                ],
            ],
            ArrayObject::ARRAY_AS_PROPS
        );

        $result = (new ToXmlTransformer())->transform($object, 0);

        $this->assertCount(2, $result->address);

        $this->assertSame(
            'Main Street',
            (string) $result->address[0]->street
        );
        $this->assertSame(
            '10',
            (string) $result->address[0]->number
        );

        $this->assertSame(
            'Second Street',
            (string) $result->address[1]->street
        );
        $this->assertSame(
            '20',
            (string) $result->address[1]->number
        );
    }

    public function testEscapesXmlValues(): void
    {
        $object = new ArrayObject(
            [
                'text' => 'A & B < C > D "quoted"',
            ],
            ArrayObject::ARRAY_AS_PROPS
        );

        $result = (new ToXmlTransformer())->transform($object, 0);

        $this->assertSame(
            'A & B < C > D "quoted"',
            (string) $result->text
        );

        $xml = $result->asXML();

        $this->assertIsString($xml);
        $this->assertStringContainsString('A &amp; B &lt; C &gt; D "quoted"', $xml);
    }
}
