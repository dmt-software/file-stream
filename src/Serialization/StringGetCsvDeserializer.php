<?php

declare(strict_types=1);

namespace DMT\FileStream\Serialization;

use ArrayObject;
use DMT\FileStream\Reader\Csv\CsvControl;
use DMT\FileStream\Reader\Property\NamingStrategyInterface;

/**
 * @implements DeserializerInterface<ArrayObject>
 */
final readonly class StringGetCsvDeserializer implements DeserializerInterface
{
    public function __construct(
        private CsvControl $control,
        private NamingStrategyInterface $namingStrategy,
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
            $this->namingStrategy->apply($records),
            ArrayObject::ARRAY_AS_PROPS
        );
    }
}
