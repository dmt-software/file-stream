<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Filter;

use DMT\FileStream\Filter\ExpressionFilter;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

#[CoversClass(ExpressionFilter::class)]
final class ExpressionFilterTest extends TestCase
{
    #[DataProvider('provideFallbackExpressions')]
    public function testEvaluateFallbackExpression(
        string $expression,
        object $object,
        bool $expected,
    ): void
    {
        $filter = new ExpressionFilter($expression);

        $this->assertSame($expected, $filter->accept($object, 0));
    }

    public function testFallbackTreatsMissingPropertyAsNull(): void
    {
        $filter = new ExpressionFilter('object.deletedAt === null');

        $this->assertTrue($filter->accept(new stdClass(), 0));
    }

    #[DataProvider('provideUnsupportedFallbackExpressions')]
    public function testRejectUnsupportedFallbackExpression(string $expression): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ExpressionFilter($expression);
    }

    public function testRejectUnsupportedFallbackValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Unsupported expression value "active"');

        new ExpressionFilter('object.status == active');
    }

    public function testEvaluateSymfonyExpression(): void
    {
        $filter = new ExpressionFilter(
            'object.age >= 18 and object.active == true',
            new ExpressionLanguage(),
        );

        $object = new stdClass();
        $object->age = 21;
        $object->active = true;

        $this->assertTrue($filter->accept($object, 0));
    }

    public function testRejectInvalidSymfonyExpressionDuringConstruction(): void
    {
        $this->expectException(LogicException::class);

        new ExpressionFilter('object.age >=', new ExpressionLanguage());
    }

    public static function provideFallbackExpressions(): iterable
    {
        return [
            'equal string' => [
                'object.status == "active"',
                (object)['status' => 'active'],
                true,
            ],

            'not equal string' => [
                'object.status != "active"',
                (object)['status' => 'inactive'],
                true,
            ],

            'identical integer' => [
                'object.age === 18',
                (object)['age' => 18],
                true,
            ],

            'not identical' => [
                'object.age !== "18"',
                (object)['age' => 18],
                true,
            ],

            'greater than' => [
                'object.age > 18',
                (object)['age' => 21],
                true,
            ],

            'greater than or equal' => [
                'object.age >= 18',
                (object)['age' => 18],
                true,
            ],

            'less than' => [
                'object.age < 18',
                (object)['age' => 17],
                true,
            ],

            'less than or equal' => [
                'object.age <= 18',
                (object)['age' => 18],
                true,
            ],

            'boolean true' => [
                'object.active === true',
                (object)['active' => true],
                true,
            ],

            'boolean false' => [
                'object.active === false',
                (object)['active' => false],
                true,
            ],

            'null' => [
                'object.deletedAt === null',
                (object)['deletedAt' => null],
                true,
            ],

            'float' => [
                'object.score >= 9.5',
                (object)['score' => 9.8],
                true,
            ],

            'negative integer' => [
                'object.temperature < -1',
                (object)['temperature' => -2],
                true,
            ],

            'single quoted string' => [
                "object.status == 'active'",
                (object)['status' => 'active'],
                true,
            ],

            'comparison fails' => [
                'object.age >= 18',
                (object)['age' => 16],
                false,
            ],
        ];
    }

    public static function provideUnsupportedFallbackExpressions(): iterable
    {
        return [
            'missing object prefix' => ['age >= 18'],
            'missing property' => ['object. >= 18'],
            'missing operator' => ['object.age 18'],
            'missing value' => ['object.age >='],
            'nested property' => ['object.user.age >= 18'],
            'unsupported operator' => ['object.age <=> 18'],
        ];
    }
}
