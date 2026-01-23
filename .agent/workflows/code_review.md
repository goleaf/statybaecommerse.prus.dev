---
description: "Workflow for reviewing changes before merge"
---

# Code Review Workflow

## 1. Scope Review
- Identify files changed and affected areas.
- Check for unrelated changes.

## 2. Security and Data
- Confirm authorization checks.
- Validate mass assignment protection.
- Validate file upload rules.

## 3. Architecture
- Ensure logic stays in Actions, Services, or Models.
- Check for N+1 queries and missing eager loading.
- Confirm translation pattern usage.

## 4. Tests and Style
- Verify relevant tests exist and were run.
- Confirm Pint formatting.

## 5. Output
- List issues by severity with file paths.
- Provide questions or assumptions if needed.
