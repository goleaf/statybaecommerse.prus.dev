# Filament Resource Test Coverage

- Keep `tests/Feature/Filament/Resources/MissingFilamentResourceCoverageTest.php` updated whenever new Filament resources are introduced or renamed so the smoke assertions continue to cover every list page.
- Call `$component->call('loadTable')` before asserting table state inside Livewire-driven tests to hydrate deferred datasets.
- Maintain descriptive inline comments in new or modified tests so reviewers can trace the intent behind helper methods and seeded fixtures.
