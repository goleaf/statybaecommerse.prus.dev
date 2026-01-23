---
description: "Workflow for triaging and fixing bugs"
---

# Bugfix Workflow

## 1. Reproduce
- Capture the exact error and reproduction steps.
- Identify the failing route, component, or test.

## 2. Triage
- Use read-log-entries and last-error for backend issues.
- Use browser-logs for JS errors.
- Use database-query for read-only inspections.

## 3. Fix
- Apply the minimal code change to resolve the root cause.
- Add or update a PHPUnit test to prevent regression.

## 4. Verify
```bash
vendor/bin/pint --dirty
php artisan test --compact --filter=[RelevantTest]
```
