<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Serialization;

use ArrayObject;
use DMT\FileStream\Config\CsvWriterConfig;
use DMT\FileStream\Record\Mapping\ColumnMapperInterface;
use DMT\FileStream\Serialization\CsvRecordSerializer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CsvRecordSerializer::class)]
final class CsvRecordSerializerTest extends TestCase
{
    public function testSerializeMappedColumns(): void
    {
        $mapper = $this->createMock(ColumnMapperInterface::class);

        $mapper
            ->expects($this->once())
            ->method('map')
            ->with([
                'name' => 'John',
                'age' => 42,
            ])
            ->willReturn(['John', 42]);

        $serializer = new CsvRecordSerializer(
            new CsvWriterConfig(),
            $mapper,
        );

        $result = $serializer->serialize(
            new ArrayObject([
                'name' => 'John',
                'age' => 42,
            ])
        );

        $this->assertSame('John,42' . "\n", $result);
    }

    #[DataProvider('provideScalarValues')]
    public function testSerializeScalarValues(mixed $value, string $expected): void
    {
        $serializer = new CsvRecordSerializer(
            new CsvWriterConfig(),
            $this->createColumnMapper([$value]),
        );

        $result = $serializer->serialize(
            new ArrayObject(['value' => $value])
        );

        $this->assertSame($expected, rtrim($result));
    }

    #[DataProvider('provideValuesRequiringEnclosure')]
    public function testEncloseValuesWhenRequired(string $value, string $expected): void
    {
        $serializer = new CsvRecordSerializer(
            new CsvWriterConfig(),
            $this->createColumnMapper([$value]),
        );

        $result = $serializer->serialize(
            new ArrayObject(['value' => $value])
        );

        $this->assertSame($expected, rtrim($result));
    }

    public function testEscapeEnclosureByDoublingIt(): void
    {
        $serializer = new CsvRecordSerializer(
            new CsvWriterConfig(),
            $this->createColumnMapper(['Say "hello"']),
        );

        $result = $serializer->serialize(
            new ArrayObject(['value' => 'Say "hello"'])
        );

        $this->assertSame('"Say ""hello"""' . "\n", $result);
    }

    public function testEscapeEnclosureUsingConfiguredEscapeCharacter(): void
    {
        $serializer = new CsvRecordSerializer(
            new CsvWriterConfig(escape: '\\'),
            $this->createColumnMapper(['Say "hello"']),
        );

        $result = $serializer->serialize(
            new ArrayObject(['value' => 'Say "hello"'])
        );

        $this->assertSame('"Say \\"hello\\""' . "\n", $result);
    }

    public function testUseConfiguredDelimiter(): void
    {
        $serializer = new CsvRecordSerializer(
            new CsvWriterConfig(delimiter: ';'),
            $this->createColumnMapper([
                'John',
                'Doe',
            ]),
        );

        $result = $serializer->serialize(
            new ArrayObject()
        );

        $this->assertSame('John;Doe' . "\n", $result);
    }

    public function testConfiguredDelimiterRequiresEnclosure(): void
    {
        $serializer = new CsvRecordSerializer(
            new CsvWriterConfig(delimiter: ';'),
            $this->createColumnMapper([
                'John;Doe',
            ]),
        );

        $result = $serializer->serialize(
            new ArrayObject()
        );

        $this->assertSame('"John;Doe"' . "\n", $result);
    }

    public function testConfiguredEnclosureIsUsed(): void
    {
        $serializer = new CsvRecordSerializer(
            new CsvWriterConfig(enclosure: "'"),
            $this->createColumnMapper([
                'John,Doe',
            ]),
        );

        $result = $serializer->serialize(
            new ArrayObject()
        );

        $this->assertSame("'John,Doe'" . "\n", $result);
    }

    public function testRejectNonArrayObject(): void
    {
        $serializer = new CsvRecordSerializer(
            new CsvWriterConfig(),
            $this->createColumnMapper([]),
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Expected ArrayObject');

        $serializer->serialize(new \stdClass());
    }

    public static function provideScalarValues(): iterable
    {
        return [
            'null' => [null, ''],
            'false' => [false, ''],
            'true' => [true, '1'],
            'integer' => [42, '42'],
            'float' => [42.5, '42.5'],
            'string' => ['value', 'value'],
        ];
    }

    public static function provideValuesRequiringEnclosure(): iterable
    {
        return [
            'delimiter' => ['John,Doe', '"John,Doe"'],
            'enclosure' => ['John "Johnny" Doe', '"John ""Johnny"" Doe"'],
            'line feed' => ["John\nDoe", "\"John\nDoe\""],
            'carriage return' => ["John\rDoe", "\"John\rDoe\""],
            'leading whitespace' => [' John', '" John"'],
            'trailing whitespace' => ['John ', '"John "'],
        ];
    }

    /**
     * @param list<scalar|null> $columns
     */
    private function createColumnMapper(array $columns): ColumnMapperInterface
    {
        $mapper = $this->createMock(ColumnMapperInterface::class);
        $mapper
            ->method('map')
            ->willReturn($columns);

        return $mapper;
    }
}