---
description: "Workflow for data imports and cleanup"
---

# Data Import and Cleanup Workflow

## 1. Define Scope
- Identify data sources and target tables.
- Confirm data format, volume, and validation rules.

## 2. Build Import Pipeline
- Use queued jobs for large imports.
- Validate inputs with Form Requests or custom validators.
- Use model factories for test fixtures.

## 3. Run in Safe Mode
- Dry-run if possible.
- Log failures with enough context to reprocess.

## 4. Cleanup
- Remove invalid rows or mark for review.
- Ensure translation rows remain consistent.

## 5. Verify
- Run targeted tests.
- Spot-check sample records via database-query.
