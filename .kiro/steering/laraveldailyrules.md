---
inclusion: manual
---

# Daily rules (optional)

- Observers: register with `#[ObservedBy(...)]` on models (don’t wire them in providers).
- Pivot tables: use alphabetical naming (e.g. `project_role`).
- Prefer `auth()->id()` and other helpers over facades when it matches existing style.
- Tests: Arrange → Act → Assert; use factories with meaningful attributes.
