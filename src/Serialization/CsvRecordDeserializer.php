<?php

declare(strict_types=1);

namespace DMT\FileStream\Serialization;

use ArrayObject;
use DMT\FileStream\Config\CsvControlInterface;
use DMT\FileStream\Record\Mapping\PropertyMapperInterface;

/**
 * Deserializes a CSV record into an ArrayObject.
 *
 * CSV parsing is configured through CsvControl. Property names are assigned
 * to the parsed values using the configured naming strategy.
 *
 * @implements DeserializerInterface<ArrayObject>
 */
final readonly class CsvRecordDeserializer implements DeserializerInterface
{
    public function __construct(
        private CsvControlInterface     $control,
        private PropertyMapperInterface $propertyNamingMapper,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function deserialize(string $data): ArrayObject
    {
        $records = str_getcsv(
            $data,
            $this->control->delimiter,
            $this->control->enclosure,
            $this->control->escape
        );

        return new ArrayObject(
            $this->propertyNamingMapper->map($records),
            ArrayObject::ARRAY_AS_PROPS
        );
    }
}
