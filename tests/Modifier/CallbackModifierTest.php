<?php

declare(strict_types=1);

namespace DMT\Test\FileStream\Modifier;

use ArrayObject;
use DMT\FileStream\Modifier\CallbackModifier;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;
use stdClass;

#[CoversClass(CallbackModifier::class)]
final class CallbackModifierTest extends TestCase
{
    public function testModifiesObject(): void
    {
        $object = new stdClass();
        $object->name = 'php';

        $modifier = new CallbackModifier(
            function (stdClass $value, int $key): stdClass {
                $value->name = strtoupper($value->name);

                return $value;
            }
        );

        $result = $modifier->modify($object, 0);

        $this->assertSame('PHP', $result->name);
    }

    public function testCanReturnReplacementInstance(): void
    {
        $object = new stdClass();
        $object->name = 'PHP';

        $replacement = new stdClass();
        $replacement->name = 'JavaScript';

        $modifier = new CallbackModifier(
            fn (stdClass $value, int $key): stdClass => $replacement
        );

        $result = $modifier->modify($object, 0);

        $this->assertSame($replacement, $result);
        $this->assertNotSame($object, $result);
    }

    public function testPassesObjectAndKeyToCallback(): void
    {
        $object = new stdClass();
        $receivedObject = null;
        $receivedKey = null;

        $modifier = new CallbackModifier(
            function (stdClass $value, int $key) use (&$receivedObject, &$receivedKey): stdClass {
                $receivedObject = $value;
                $receivedKey = $key;

                return $value;
            }
        );

        $modifier->modify($object, 42);

        $this->assertSame($object, $receivedObject);
        $this->assertSame(42, $receivedKey);
    }

    public function testThrowsExceptionWhenObjectTypeDoesNotMatchCallback(): void
    {
        $modifier = new CallbackModifier(
            fn (SimpleXMLElement $object, int $key): SimpleXMLElement => $object
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs(
            'Callback not compatible with ObjectReader'
        );

        $modifier->modify(new ArrayObject(), 0);
    }
}
