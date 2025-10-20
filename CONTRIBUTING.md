# Contributing

## Testing

Run the automated test suite with `composer test`. This executes the project-standard PHPUnit configuration and stores local coverage data in `storage/app/coverage`.

Continuous integration uses `composer test:ci` to generate machine-readable reports in the `build/` directory. You can run the same command locally when you need the JUnit or Clover artifacts.

## Caching

Review the [Cache Policy](docs/CachePolicy.md) for TTL guidelines, tag usage, and instructions on extending the `CacheKeys` helper before introducing new cache entries.
