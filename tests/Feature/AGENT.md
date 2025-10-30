# Feature Test Guidelines

- Always call `$component->call('loadTable')` before asserting against Filament table state so deferred datasets hydrate correctly in the Livewire harness.
- When you need to check per-record visibility for a table action, fetch it via `$component->instance()->getTable()->getAction('action_name')`, bind the target record with `$action->record($record)`, and assert `isVisible()` or `isHidden()` directly. The `callTableAction()` helper fails fast when the action is hidden, so visibility expectations must interrogate the action instance.
- Maintain descriptive inline comments explaining each assertion so the test intent stays obvious during future refactors.
