# Filament Resource Test Coverage

- Keep `tests/Feature/Filament/Resources/MissingFilamentResourceCoverageTest.php` updated whenever new Filament resources are introduced or renamed so the smoke assertions continue to cover every list page.
- Keep `tests/Feature/Filament/Resources/MissingFilamentResourceCoverageTest.php` updated whenever new Filament resources are introduced or renamed so the smoke assertions continue to cover every list page. When adding duplicate list pages (for example both legacy and v4 namespaces), reuse cached factory helpers so unique slugs and codes are only seeded once.
- Call `$component->call('loadTable')` before asserting table state inside Livewire-driven tests to hydrate deferred datasets.
- Maintain descriptive inline comments in new or modified tests so reviewers can trace the intent behind helper methods and seeded fixtures.
- Seed the shared `RolesAndPermissionsSeeder` and call `$this->resolveAdminPanel()` in test `setUp()` implementations when resources rely on policy gates so Livewire components authenticate against the Filament guard without manual stubs.
- Update `tests/Feature/Filament/Resources/RoleResourceGuardOptionsTest.php` when the guard list changes so Filament guard selectors stay in sync with the authorization matrix.
