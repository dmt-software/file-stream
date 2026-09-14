<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Serialization;

use DMT\FileStream\Config\CsvReaderConfig;
use DMT\FileStream\Record\Mapping\PropertyMapperInterface;
use DMT\FileStream\Serialization\CsvRecordDeserializer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CsvRecordDeserializer::class)]
final class CsvRecordDeserializerTest extends TestCase
{
    public function testDeserializeCsvRecord(): void
    {
        $mapper = new class implements PropertyMapperInterface {
            public function map(array $values): array
            {
                return [
                    'firstName' => $values[0],
                    'lastName' => $values[1],
                ];
            }
        };

        $deserializer = new CsvRecordDeserializer(
            new CsvReaderConfig(),
            $mapper,
        );

        $result = $deserializer->deserialize('John,Doe');

        $this->assertSame('John', $result->firstName);
        $this->assertSame('Doe', $result->lastName);
    }

    public function testDeserializeUsingCustomDelimiter(): void
    {
        $mapper = new class implements PropertyMapperInterface {
            public function map(array $values): array
            {
                return [
                    'name' => $values[0],
                    'age' => $values[1],
                ];
            }
        };

        $deserializer = new CsvRecordDeserializer(
            new CsvReaderConfig( delimiter: ';'),
            $mapper,
        );

        $result = $deserializer->deserialize('John;42');

        $this->assertSame('John', $result->name);
        $this->assertSame('42', $result->age);
    }

    public function testDeserializeEnclosedValue(): void
    {
        $mapper = new class implements PropertyMapperInterface {
            public function map(array $values): array
            {
                return [
                    'name' => $values[0],
                    'address' => $values[1],
                ];
            }
        };

        $deserializer = new CsvRecordDeserializer(
            new CsvReaderConfig(),
            $mapper,
        );

        $result = $deserializer->deserialize(
            'John,"Main street, 10"'
        );

        $this->assertSame('John', $result->name);
        $this->assertSame('Main street, 10', $result->address);
    }

    public function testPassParsedValuesToPropertyMapper(): void
    {
        $mapper = $this->createMock(PropertyMapperInterface::class);

        $mapper
            ->expects($this->once())
            ->method('map')
            ->with(['John', 'Doe', '42'])
            ->willReturn([
                'firstName' => 'John',
                'lastName' => 'Doe',
                'age' => '42',
            ]);

        $deserializer = new CsvRecordDeserializer(
            new CsvReaderConfig(),
            $mapper,
        );

        $result = $deserializer->deserialize('John,Doe,42');

        $this->assertSame(
            [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'age' => '42',
            ],
            $result->getArrayCopy(),
        );
    }
}
