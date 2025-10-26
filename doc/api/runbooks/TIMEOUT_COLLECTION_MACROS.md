# Collection Timeout Macros

This note documents the shared `takeUntilTimeout` collection macro that keeps background jobs
and storefront controllers safe when iterating large datasets. The helper now ships as part of
`AppServiceProvider` and is available for both `Illuminate\\Support\\Collection` and
`Illuminate\\Support\\LazyCollection` instances.

## Why the macro exists

Long running imports and exports rely on timed exits so they release queue workers before the
next scheduled task is due. The macro stops iteration as soon as the configured timeout window
is reached, avoiding job timeouts and giving us deterministic behaviour in tests.

## Usage

```php
$deadline = now()->addSeconds(30);

// Works with lazy collections, ideal for cursor-based queries
Product::cursor()
    ->takeUntilTimeout($deadline)
    ->each(fn ($product) => export_product($product));

// Works with eager collections as well
collect($items)
    ->takeUntilTimeout($deadline)
    ->each(fn ($item) => dispatch($item));
```

## Behaviour highlights

- Accepts either `Carbon` instances, `DateTimeInterface` values, numeric second offsets, or
  `DateInterval` objects.
- Returns a lazy collection in both cases so downstream code can continue streaming results.
- Short-circuits immediately when the timeout is already in the past, yielding an empty
  iterator.
- Leaves the original collection untouched and clones `Carbon` instances before using them so
  callers can safely reuse their deadline in logs or metrics.

## Integration details

The macro is registered from `AppServiceProvider::registerCollectionTimeoutMacros()` during
application boot. Any command, job, or test that resolves the application container will have
access to the helper without additional setup.
