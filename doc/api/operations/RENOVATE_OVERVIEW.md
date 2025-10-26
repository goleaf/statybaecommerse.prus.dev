# Renovate Automation Overview

Renovate keeps PHP and Node dependencies fresh while respecting the
project's release cadence. The configuration introduced in
`.github/renovate.json5` enforces the following guardrails:

- **Weekly rollups:** Composer and npm updates are batched after 02:00
  on Mondays (Europe/Vilnius) to align with our existing maintenance
  window.
- **Stable channels only:** Laravel Framework updates stay on the
  12.x release line and Filament packages remain within 4.x so that the
  admin panel conventions documented elsewhere remain valid.
- **Renovate PR checks:** Any pull request opened by Renovate runs the
  PHPUnit workflow on PHP 8.2 and 8.3, matching the compatibility window
  we support in production.

When you need to tweak automation defaults, edit
`.github/renovate.json5` and cross-check this note so the documentation
and the pipeline remain in sync.
