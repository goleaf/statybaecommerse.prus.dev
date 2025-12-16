---
inclusion: fileMatch
fileMatchPattern: 'resources/views/**/*.blade.php'
---

# Blade + Tailwind rules (scoped)

## Blade
- Don’t hardcode user-facing strings; use translations (ensure `lt` + `en` keys exist).
- Keep templates simple; move complex logic to view models/components.

## TailwindCSS v4
- Prefer existing utility patterns in this repo.
- For lists/layout spacing, prefer `gap-*` on parent containers over per-item margins.
- Keep dark mode consistent if already used (`dark:` utilities).
