# Transformers

Transformers convert an object from one representation into another.

```php
/**
 * @template T of object
 * @template R of object
 */
interface TransformerInterface
{
    /**
     * @param T $object
     * @return R
     */
    public function transform(object $object): object;
}
```

A transformer represents a structural conversion:

```text
T → R
```

## ToArrayObjectTransformer

```text
stdClass|SimpleXMLElement
→ ArrayObject
```

This transformer is useful before CSV serialization.

Nested structures are flattened with underscore-separated names:

```text
address.street
→ address_street
```

Scalar values and `null` are preserved. Scalar lists remain arrays so repeated CSV columns can consume them.

For `SimpleXMLElement`, leaf elements are normalized to their string content before being added.

## ToJsonTransformer

```text
ArrayObject|SimpleXMLElement
→ stdClass
```

Nested structures become nested `stdClass` objects. Repeated XML elements become arrays.

Example:

```xml
<user>
    <tag>one</tag>
    <tag>two</tag>
    <address>
        <street>Main Street</street>
    </address>
    <address>
        <street>Second Street</street>
    </address>
</user>
```

becomes structurally equivalent to:

```php
(object) [
    'tag' => ['one', 'two'],
    'address' => [
        (object) ['street' => 'Main Street'],
        (object) ['street' => 'Second Street'],
    ],
]
```

## ToXmlTransformer

```text
ArrayObject|stdClass
→ SimpleXMLElement
```

A configurable root element is created for each object.

```php
$transformer = new ToXmlTransformer(
    rootElement: 'user'
);
```

Arrays become repeated sibling elements:

```php
[
    'tag' => ['one', 'two'],
]
```

becomes:

```xml
<user>
    <tag>one</tag>
    <tag>two</tag>
</user>
```

Nested objects become nested elements.
