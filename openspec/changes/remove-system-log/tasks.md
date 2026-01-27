## 1. Implementation
- [x] 1.1 Confirm there are no runtime references to `App\Models\SystemLog` or the `system_logs` table via `rg` and code inspection.
- [x] 1.2 Remove the `App\Models\SystemLog` model.
- [x] 1.3 Add a new forward-only migration that drops `system_logs` if it exists.
- [x] 1.4 Remove `system_logs` creation blocks from historical migrations to prevent it being recreated in fresh installs.
- [x] 1.5 Ensure migration teardown paths remain safe and do not attempt to drop missing tables.

## 2. Validation
- [x] 2.1 Run `composer run test`.
- [x] 2.2 Run `composer run analyze`.
- [x] 2.3 Run `openspec validate remove-system-log --strict`.
