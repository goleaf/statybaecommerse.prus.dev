---
inclusion: manual
---

# Laravel rules (optional)

- Prefer Form Requests for validation.
- Enforce authorization via policies/gates and test forbidden paths.
- Avoid calling `env()` outside config; use `config()` at runtime.
- Prefer eager loading to avoid N+1 queries.
