<?php

declare(strict_types=1);

namespace DMT\FileStream\Serialization\Mapping\Csv;

use DMT\FileStream\Serialization\Mapping\PropertyMapperInterface;

/**
 * Maps positional columns to spreadsheet-style property names.
 *
 * Column indexes are converted to alphabetical names such as A, B, Z, AA and
 * AB. This makes positional data easier to reference and map using familiar
 * spreadsheet-style column names.
 *
 * The generated names are determined from the first row and then reused for
 * subsequent rows.
 */
final class SpreadsheetColumnPropertyMapper implements PropertyMapperInterface
{
    /**
     * Construct the strategy based on the first row.
     */
    private ?PredefinedPropertyMapper $mapper = null;

    /**
     * Map the given record values to named properties.
     *
     * @param list<mixed> $values
     * @return array<string, mixed>
     */
    public function map(array $values): array
    {
        $this->mapper ??= new PredefinedPropertyMapper(
            array_map($this->getColumnName(...), array_keys($values))
        );

        return $this->mapper->map($values);
    }

    private function getColumnName(int $index, string $name = ''): string
    {
        $c = $index % 26;

        if ($index >= 26) {
            $name .= $this->getColumnName((($index - $c) / 26) - 1, $name);
        }

        $name .= chr($c + 65);

        return $name;
    }
}
