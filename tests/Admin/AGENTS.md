# Admin Filament Test Guidelines

- Seed the Filament admin panel and assign the required Spatie permissions (view/create/edit/delete) to the acting user before interacting with Customer-focused Livewire components; policy checks will otherwise fail.
- Prefer factories when creating related models (countries, cities, companies) so schema drift is respected by RefreshDatabase migrations instead of hand-crafted table definitions.
- When asserting table state, call `loadTable()` before exercising filters or actions to hydrate deferred table data in Livewire-driven tests.
