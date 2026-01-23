---
description: "Workflow for building API endpoints"
---

# API Endpoint Workflow

## 1. Define Contract
- Confirm request payload, validation rules, and response shape.
- Decide on API version and route naming.

## 2. Generate Files
- Use php artisan make:request and make:resource --no-interaction.

## 3. Implement
- Use Form Requests for validation and custom messages.
- Use API Resources for responses.
- Apply authorization via Policies or Gates.

## 4. Tests
- Feature tests for success, validation failures, and authorization failures.

## 5. Verify
```bash
vendor/bin/pint --dirty
php artisan test --compact --filter=[TestClassName]
```
