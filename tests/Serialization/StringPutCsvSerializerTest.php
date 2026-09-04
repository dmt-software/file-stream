<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Serialization;

use ArrayObject;
use DMT\FileStream\Csv\CsvControl;
use DMT\FileStream\Serialization\StringPutCsvSerializer;
use DMT\FileStream\Writer\Column\ColumnStrategyInterface;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(StringPutCsvSerializer::class)]
final class StringPutCsvSerializerTest extends TestCase
{
    public function testSerializesColumns(): void
    {
        $strategy = $this->createMock(ColumnStrategyInterface::class);
        $strategy
            ->expects($this->once())
            ->method('apply')
            ->with(['name' => 'PHP', 'since' => 1995])
            ->willReturn(['PHP', 1995]);

        $serializer = new StringPutCsvSerializer(
            control: new CsvControl(),
            columnStrategy: $strategy
        );

        $this->assertSame(
            'PHP,1995',
            $serializer->serialize(
                new ArrayObject(['name' => 'PHP', 'since' => 1995])
            )
        );
    }

    #[DataProvider('scalarProvider')]
    public function testSerializesScalarValues(mixed $value, string $expected): void
    {
        $serializer = $this->serializer([$value]);

        $this->assertSame($expected, $serializer->serialize(new ArrayObject()));
    }

    /**
     * @return iterable<string, array{mixed, string}>
     */
    public static function scalarProvider(): iterable
    {
        yield 'null' => [null, ''];
        yield 'true' => [true, '1'];
        yield 'false' => [false, ''];
        yield 'integer' => [42, '42'];
        yield 'float' => [42.5, '42.5'];
        yield 'string' => ['PHP', 'PHP'];
        yield 'internal space' => ['PHP language', 'PHP language'];
    }

    #[DataProvider('enclosureProvider')]
    public function testEnclosesStringsWhenRequired(string $value, string $expected): void {
        $serializer = $this->serializer([$value]);

        $this->assertSame($expected, $serializer->serialize(new ArrayObject()));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function enclosureProvider(): iterable
    {
        yield 'delimiter' => ['PHP,JavaScript', '"PHP,JavaScript"'];
        yield 'enclosure' => ['PHP "language"', '"PHP ""language"""'];
        yield 'line feed' => ["PHP\nlanguage", "\"PHP\nlanguage\""];
        yield 'carriage return' => ["PHP\rlanguage", "\"PHP\rlanguage\""];
        yield 'leading whitespace' => [' PHP', '" PHP"'];
        yield 'trailing whitespace' => ['PHP ', '"PHP "'];
    }

    public function testUsesConfiguredDelimiterAndEnclosure(): void
    {
        $serializer = $this->serializer(
            columns: ['PHP;language'],
            control: new CsvControl(delimiter: ';', enclosure: "'")
        );

        $this->assertSame(
            "'PHP;language'",
            $serializer->serialize(new ArrayObject())
        );
    }

    public function testUsesConfiguredEscapeCharacter(): void
    {
        $serializer = $this->serializer(
            columns: ['PHP "language"'],
            control: new CsvControl(escape: '\\')
        );

        $this->assertSame(
            '"PHP \"language\""',
            $serializer->serialize(new ArrayObject())
        );
    }

    public function testJoinsMultipleColumnsWithConfiguredDelimiter(): void
    {
        $serializer = $this->serializer(
            columns: ['PHP', 1995, 'Rasmus Lerdorf'],
            control: new CsvControl(delimiter: ';')
        );

        $this->assertSame(
            'PHP;1995;Rasmus Lerdorf',
            $serializer->serialize(new ArrayObject())
        );
    }

    public function testRejectsObjectOtherThanArrayObject(): void
    {
        $strategy = $this->createStub(ColumnStrategyInterface::class);

        $serializer = new StringPutCsvSerializer(
            control: new CsvControl(),
            columnStrategy: $strategy
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Expected ArrayObject');

        $serializer->serialize(new \stdClass());
    }

    /**
     * @param list<scalar|null> $columns
     */
    private function serializer(array $columns, ?CsvControl $control = null): StringPutCsvSerializer
    {
        $strategy = $this->createStub(ColumnStrategyInterface::class);
        $strategy
            ->method('apply')
            ->willReturn($columns);

        return new StringPutCsvSerializer(
            control: $control ?? new CsvControl(),
            columnStrategy: $strategy
        );
    }
}
