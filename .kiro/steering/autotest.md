---
inclusion: fileMatch
fileMatchPattern: 'app/**/*.php'
---

# Tests for app changes (scoped)

When editing PHP classes under `app/**`, create/update tests that cover the changed behavior.

## Map code → tests
- `App\Models\*` → `tests/Unit` (casts, accessors, scopes, relationships, domain logic).
- Controllers/HTTP actions → `tests/Feature` (auth, validation, redirects/status, DB writes, events).
- Livewire/Filament components → `tests/Feature` (mount/render, actions, validation, authz).

## Test style
- Use Pest (match existing conventions).
- Use factories with meaningful values (better failure messages).
- Cover a happy-path + at least one failure/edge case for each public behavior.

## Verification loop
- Run the narrowest relevant test(s) first, then expand scope if needed/requested.
- When unsure of APIs, use Boost `search-docs` before guessing.
