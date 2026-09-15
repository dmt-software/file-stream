<?php

declare(strict_types=1);

namespace DMT\FileStream\Filter\Operation;

use Closure;

enum ComparisonOperator: string
{
    case Equal = '==';
    case Identical = '===';
    case NotEqual = '!=';
    case NotIdentical = '!==';
    case GreaterThan = '>';
    case GreaterThanOrEqual = '>=';
    case LessThan = '<';
    case LessThanOrEqual = '<=';

    /**
     * Get the comparator for this operation.
     *
     * @return Closure(mixed, mixed): bool
     */
    public function getComparator(): Closure
    {
        return match ($this) {
            self::Equal => static fn (mixed $a, mixed $b): bool => $a == $b,
            self::Identical => static fn (mixed $a, mixed $b): bool => $a === $b,
            self::NotEqual => static fn (mixed $a, mixed $b): bool => $a != $b,
            self::NotIdentical => static fn (mixed $a, mixed $b): bool => $a !== $b,
            self::GreaterThan => static fn (mixed $a, mixed $b): bool => $a > $b,
            self::GreaterThanOrEqual => static fn (mixed $a, mixed $b): bool => $a >= $b,
            self::LessThan => static fn (mixed $a, mixed $b): bool => $a < $b,
            self::LessThanOrEqual => static fn (mixed $a, mixed $b): bool => $a <= $b,
        };
    }

    public function compare(mixed $a, mixed $b): bool
    {
        return ($this->getComparator())($a, $b);
    }
}
