<?php

declare(strict_types=1);

namespace DMT\FileStream\Transformer;

use ArrayObject;
use SimpleXMLElement;
use stdClass;

/**
 * @implements TransformerInterface<ArrayObject|SimpleXMLElement, stdClass>
 */
final class ToJsonTransformer implements TransformerInterface
{
    /**
     * @inheritDoc
     */
    public function transform(object $object): stdClass
    {
        return $this->transformObject($object);
    }

    private function transformObject(object $object): stdClass
    {
        $result = new stdClass();

        foreach ($object as $name => $value) {
            $this->appendValue($result, (string) $name, $value);
        }

        return $result;
    }

    private function appendValue(stdClass $result, string $name, mixed $value): void
    {
        $value = $this->transformValue($value);

        if (!property_exists($result, $name)) {
            $result->{$name} = $value;

            return;
        }

        if (!is_array($result->{$name})) {
            $result->{$name} = [$result->{$name}];
        }

        $result->{$name}[] = $value;
    }

    private function transformValue(mixed $value): mixed
    {
        if ($value instanceof SimpleXMLElement) {
            if ($value->count() === 0) {
                return (string) $value;
            }

            return $this->transformObject($value);
        }

        if ($value instanceof ArrayObject) {
            return $this->transformObject($value);
        }

        if (is_array($value)) {
            return array_map($this->transformValue(...), $value);
        }

        return $value;
    }
}
