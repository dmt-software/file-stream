<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Filter;

use DMT\FileStream\Filter\Operation\ComparisonOperator;
use DMT\FileStream\Filter\PropertyFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(PropertyFilter::class)]
final class PropertyFilterTest extends TestCase
{
    #[DataProvider('provideComparisons')]
    public function testCompareProperty(
        ComparisonOperator $operator,
        mixed $actual,
        mixed $expected,
        bool $accepted,
    ): void {
        $filter = new PropertyFilter('value', $expected, $operator);

        $object = new stdClass();
        $object->value = $actual;

        $this->assertSame($accepted, $filter->accept($object, 0));
    }

    public function testMissingPropertyIsComparedAsNull(): void
    {
        $filter = new PropertyFilter('missing', null, ComparisonOperator::Identical);

        $this->assertTrue($filter->accept(new stdClass, 0));
    }

    public static function provideComparisons(): iterable
    {
        return [
            'equal' => [ComparisonOperator::Equal, 42, '42', true],
            'identical' => [ComparisonOperator::Identical, 42, 42, true],
            'not equal' => [ComparisonOperator::NotEqual, 42, 43, true],
            'not identical' => [ComparisonOperator::NotIdentical, 42, '42', true],
            'greater than' => [ComparisonOperator::GreaterThan, 42, 40, true],
            'greater than or equal' => [ComparisonOperator::GreaterThanOrEqual, 42, 42, true],
            'less than' => [ComparisonOperator::LessThan, 40, 42, true],
            'less than or equal' => [ComparisonOperator::LessThanOrEqual, 42, 42, true]
        ];
    }
}
