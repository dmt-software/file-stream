<?php

declare(strict_types=1);

namespace DMT\FileStream\Transformer;

use ArrayObject;
use SimpleXMLElement;
use stdClass;

/**
 * @implements TransformerInterface<ArrayObject|stdClass, SimpleXMLElement>
 */
final readonly class ToXmlTransformer implements TransformerInterface
{
    public function __construct(
        private string $rootElement = 'result',
    ) {
    }

    /**
     * @inheritDoc
     */
    public function transform(object $object): SimpleXMLElement
    {
        $xml = new SimpleXMLElement(sprintf('<%1$s></%1$s>', $this->rootElement));

        $this->appendObject($xml, $object);

        return $xml;
    }

    private function appendObject(SimpleXMLElement $xml, object $object): void
    {
        foreach ($object as $name => $value) {
            $this->appendValue($xml, (string) $name, $value);
        }
    }

    private function appendValue(SimpleXMLElement $xml, string $name, mixed $value): void
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                $this->appendValue($xml, $name, $item);
            }

            return;
        }

        if ($value instanceof ArrayObject || $value instanceof stdClass) {
            $child = $xml->addChild($name);

            $this->appendObject($child, $value);

            return;
        }

        $xml->addChild(
            $name,
            $value === null ? '' : htmlspecialchars((string) $value, ENT_XML1, 'UTF-8')
        );
    }
}