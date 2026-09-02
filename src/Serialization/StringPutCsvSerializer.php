<?php

declare(strict_types=1);

namespace DMT\FileStream\Serialization;

use ArrayObject;
use DMT\FileStream\Csv\CsvControl;
use DMT\FileStream\Writer\Column\ColumnStrategyInterface;
use InvalidArgumentException;

/**
 * Serializes an ArrayObject into a CSV record.
 *
 * @implements SerializerInterface<ArrayObject<string, mixed>>
 */
final readonly class StringPutCsvSerializer implements SerializerInterface
{
    public function __construct(
        private CsvControl $control,
        private ColumnStrategyInterface $columnStrategy,
    ) {
    }

    /**
     * @param ArrayObject<string, mixed> $object
     */
    public function serialize(object $object): string
    {
        if (!$object instanceof ArrayObject) {
            throw new InvalidArgumentException('Expected ArrayObject');
        }

        $columns = $this->columnStrategy->apply(
            $object->getArrayCopy()
        );

        return implode(
            $this->control->delimiter,
            array_map($this->serializeColumn(...), $columns)
        );
    }

    /**
     * @param scalar|null $value
     */
    private function serializeColumn(mixed $value): string
    {
        $value = match (get_debug_type($value)) {
            'null' => '',
            'bool' => $value ? '1' : '',
            'int', 'float' => (string)$value,
            'string' => $value,
        };

        if (!$this->requiresEnclosure($value)) {
            return $value;
        }

        return $this->control->enclosure . $this->escapeEnclosure($value) . $this->control->enclosure;
    }

    private function requiresEnclosure(string $value): bool
    {
        return str_contains($value, $this->control->delimiter)
            || str_contains($value, $this->control->enclosure)
            || str_contains($value, "\r")
            || str_contains($value, "\n")
            || $value !== trim($value);
    }

    private function escapeEnclosure(string $value): string
    {
        $replacement = $this->control->escape === ''
            ? $this->control->enclosure . $this->control->enclosure
            : $this->control->escape . $this->control->enclosure;

        return str_replace(
            $this->control->enclosure,
            $replacement,
            $value
        );
    }
}
