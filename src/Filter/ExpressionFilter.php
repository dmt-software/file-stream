<?php

declare(strict_types=1);

namespace DMT\FileStream\Filter;

use DMT\FileStream\Filter\Operation\ComparisonOperator;
use InvalidArgumentException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use ValueError;

/**
 * Filters objects using an expression.
 *
 * When an ExpressionLanguage instance is supplied, the expression is validated
 * and evaluated using Symfony ExpressionLanguage. Otherwise, a limited object
 * property comparison is supported and delegated to PropertyFilter.
 *
 * Supported fallback examples:
 *
 *     object.age >= 18
 *     object.status == "active"
 *     object.deletedAt === null
 *
 * @template T of object
 * @implements FilterInterface<T>
 */
final readonly class ExpressionFilter implements FilterInterface
{
    private const string EXPRESSION_PATTERN =
        '~^\s*object\.([a-z_][a-z0-9_]*)\s*([!=><]{1,3})\s*(.+?)\s*$~xi';

    private ?PropertyFilter $filter;

    public function __construct(
        private string $expression,
        private ?ExpressionLanguage $expressionLanguage = null,
    ) {
        if ($this->expressionLanguage !== null) {
            $this->expressionLanguage->lint($this->expression, ['object']);

            $this->filter = null;

            return;
        }

        if (!preg_match(self::EXPRESSION_PATTERN, $this->expression, $matches)) {
            throw new InvalidArgumentException('Unsupported filter expression');
        }

        array_shift($matches);

        [$property, $operator, $value] = $matches;

        try {
            $operator = ComparisonOperator::from($operator);
        } catch (ValueError) {
            throw new InvalidArgumentException('Unsupported operator in expression');
        }

        $this->filter = new PropertyFilter(
            $property,
            $this->parseValue($value),
            $operator
        );
    }

    public function accept(object $object, int $key): bool
    {
        if ($this->expressionLanguage !== null) {
            return (bool) $this->expressionLanguage->evaluate(
                $this->expression, ['object' => $object]
            );
        }

        return $this->filter->accept($object, $key);
    }

    private function parseValue(string $value): mixed
    {
        return match (true) {
            $value === 'null' => null,
            $value === 'true' => true,
            $value === 'false' => false,
            preg_match('~^-?\d+$~', $value) === 1 => (int) $value,
            is_numeric($value) => (float) $value,
            $this->isQuoted($value) => stripcslashes(substr($value, 1, -1)),
            default => throw new InvalidArgumentException(
                sprintf('Unsupported expression value "%s"', $value)
            ),
        };
    }

    private function isQuoted(string $value): bool
    {
        if (strlen($value) < 2) {
            return false;
        }

        return ($value[0] === '"' && $value[-1] === '"')
            || ($value[0] === "'" && $value[-1] === "'");
    }
}