# User Behaviour Models Update

## Summary
- Normalised the `UserPreference` model so alias attributes (`name`, `key`, `value`, `meta`) map cleanly onto the stored columns while preserving manual `last_updated` timestamps and null metadata states for factories and fixtures.【F:app/Models/UserPreference.php†L55-L169】
- Added explicit JSON normalisation for the `metadata` column so callers receive arrays only when data exists, ensuring tests that expect `null` continue to pass after schema casts were trimmed.【F:app/Models/UserPreference.php†L123-L210】
- Hardened the `UserPreference` metadata mutator to JSON-encode payloads before persistence so SQLite and MySQL bindings stay consistent while alias accessors continue to expose native arrays to consumers.【F:app/Models/UserPreference.php†L170-L210】
- Refined `UserProductInteraction` meta handling to accept legacy payload shapes, propagate rating/count columns, and persist arrays directly for the JSON column to stabilise downstream analytics consumers.【F:app/Models/UserProductInteraction.php†L31-L187】
- Hardened `UserProductInteraction` persistence so array payloads are JSON-encoded before storage, preventing SQLite binding errors during reporting workflows while preserving legacy column mirrors.【F:app/Models/UserProductInteraction.php†L119-L208】
- Defaulted `UserProductInteraction` timestamps so inserts that only provide the consolidated `occurred_at` value automatically backfill `first_interaction`/`last_interaction`, satisfying the legacy schema constraints without extra caller logic.【F:app/Models/UserProductInteraction.php†L63-L102】

## Follow-up Ideas
- Extend model factories with named states that exercise alias-based mass assignment to guard against regressions in the hydrators.
- Layer dedicated query scopes for minimum rating/count thresholds in analytics dashboards using the cleaned payload helpers.
