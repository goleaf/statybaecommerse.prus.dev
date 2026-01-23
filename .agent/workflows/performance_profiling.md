---
description: "Workflow for profiling and improving performance"
---

# Performance Profiling Workflow

## 1. Define the Slow Path
- Identify the endpoint, query, or component that is slow.
- Capture timing data and reproduction steps.

## 2. Inspect Queries
- Use database-query for read-only checks.
- Look for N+1 and missing eager loading.

## 3. Optimize
- Add eager loading with with() or load().
- Add indexes where appropriate (foreign keys, locale, filters).
- Cache expensive calculations or queries.

## 4. Verify
- Re-test the slow path and compare timings.
- Add or update tests if behavior changes.
