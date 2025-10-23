# Feature Highlights

## Admin panel resilience
- Docblock-based Filament navigation icons are reaffirmed to avoid enum property collisions introduced in PR #533.
- Variant stock history change reasons now map both `damage` and `theft` outcomes to the `danger` badge without duplicate keys, keeping badge colors predictable.

## Tooling polish
- The `data:import` Artisan command exposes typed signature and description properties so maintainers can quickly identify its purpose when running `php artisan list`.
