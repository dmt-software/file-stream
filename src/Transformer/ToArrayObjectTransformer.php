<?php

declare(strict_types=1);

namespace DMT\FileStream\Transformer;

use ArrayObject;
use SimpleXMLElement;
use stdClass;

/**
 * @implements TransformerInterface<stdClass|SimpleXMLElement, ArrayObject>
 */
class ToArrayObjectTransformer implements TransformerInterface
{

    /**
     * @inheritDoc
     */
    public function transform(object $object): ArrayObject
    {
        $result = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);

        foreach ($object as $name => $value) {
            $this->appendValue($result, $name, $value);
        }

        return $result;
    }

    private function appendValue(ArrayObject $result, string $name, mixed $value): void
    {
        if (is_array($value) && count($value) === 1) {
            $value = array_shift($value);
        }

        if ($value instanceof SimpleXMLElement && $value->count() === 0) {
            $value = (string) $value;
        }

        if (is_scalar($value) || is_null($value)) {
            $result->offsetSet($name, $value);
            return;
        }

        if (is_array($value) && array_is_list($value)) {
            $value = array_filter($value, fn($item) => is_scalar($item) || is_null($item));

            $result->offsetSet($name, array_values($value));

            return;
        }

        foreach ($value as $key => $val) {
            $this->appendValue($result, $name . '_' . $key, $val);
        }
    }
}
