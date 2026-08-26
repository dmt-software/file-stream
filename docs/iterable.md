# Iterable

`IterableReader` allows any PHP iterable to be used with the same processing pipeline as the file-based readers.

This is useful for sources such as:

- database cursors;
- Doctrine iterables;
- generators;
- API result streams;
- arrays of objects.

The iterable is consumed lazily and is not materialized into an array.

## Basic usage

Given an iterable of objects:

```php
use DMT\FileStream\Reader\IterableReader;

$reader = new IterableReader(
    fetchUsers(),
);

foreach ($reader->getResults() as $user) {
    echo $user->email;
}
```

For example, a database-backed generator could look like:

```php
function fetchUsers(): iterable
{
    while ($row = $statement->fetch()) {
        yield hydrateUser($row);
    }
}
```

`IterableReader` consumes each item only when iteration advances.

## Using Processor

Because `IterableReader` implements `ReaderInterface`, it can be passed directly to `Processor`:

```php
use DMT\FileStream\Processor;
use DMT\FileStream\Reader\IterableReader;

$reader = new IterableReader(
    fetchUsers(),
);

$processor = new Processor($reader);

$processor->filter(
    fn (User $user) => $user->active
);

$processor->limit(
    offset: 100,
    limit: 50,
);

foreach ($processor->getResults() as $user) {
    // process streamed result
}
```

Filters are applied before offset and limit, just like with the file-based readers.

## Header

An iterable does not necessarily have a natural header.

A header can optionally be supplied when creating the reader:

```php
$header = new ImportHeader();
$header->source = 'database';
$header->version = 2;

$reader = new IterableReader(
    fetchUsers(),
    $header,
);
```

The header can then be used for validation:

```php
$processor = new Processor($reader);

$processor->validate(
    fn (ImportHeader $header) =>
        $header->version === 2
);
```

If no header was supplied, calling:

```php
$reader->getHeader();
```

throws `NotFoundException`.

## Supported iterables

Any PHP iterable yielding objects can be used.

### Generator

```php
$reader = new IterableReader(
    (function () {
        yield new Result();
        yield new Result();
    })()
);
```

### Array

```php
$reader = new IterableReader([
    new Result(),
    new Result(),
]);
```

### Database cursor

```php
$reader = new IterableReader(
    $repository->iterateResults(),
);
```

As long as the source itself is lazy, the results remain lazy throughout `IterableReader` and `Processor`.

## Streaming behavior

`IterableReader` follows the same single-pass model as the other streaming readers.

Once result iteration has started, the reader cannot be rewound and consumed a second time.

Attempting to read the results again throws `ReaderException`.

This is particularly important for database cursors and generators, which are naturally forward-only.

## Result types

`IterableReader` expects the iterable to yield objects.

For example:

```php
/** @return iterable<User> */
function fetchUsers(): iterable
{
    // ...
}
```

can be passed directly to:

```php
$reader = new IterableReader(
    fetchUsers(),
);
```

The result object type is preserved through the reader and processor for static analysis.