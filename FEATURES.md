# Platform Features & Highlights

This snapshot complements the changelog by listing functional capabilities that ship with the storefront and admin panel.

## Core Commerce Platform
- Laravel 12 + Filament v4 admin with multilingual product, pricing, discount, and order management flows.
- Customer loyalty, referral tracking, and recommendation engines with configurable targeting rules.
- Automated media processing, queue orchestration, and analytics dashboards for store operators.

## Storefront Experience
- Livewire-powered storefront pages with localisation, SEO metadata, and responsive catalogue browsing.
- Checkout, cart persistence, and account management journeys wired to the same aggregates used in the admin UI.

## Operational Tooling
- Queue, cache, and deployment runbooks collected under [`docs/runbooks/`](docs/runbooks/) for production readiness.
- API contracts, payload samples, and integration notes organised in [`docs/contracts/`](docs/contracts/).
- Analytical reports, project retrospectives, and rollout summaries consolidated inside [`docs/analysis/`](docs/analysis/).

## Latest Update
- Developer tooling once again ships with the Husky bootstrap shim under version control, keeping Git hooks active after fresh clones while flagging the upcoming v10 script migration path for contributors.
- Middleware-delivered CSP nonces now flow through Livewire, Vite, and inline Blade assets alongside stricter HSTS and permissions policy defaults, raising the platform's baseline security posture.
- API error responses now surface an explicit `error.rate_limited` code for HTTP 429 cases so partner integrations can detect throttling conditions without parsing status text.
- Test infrastructure now reloads JSON translation directories so Filament commerce navigation continues to present localized labels during automated checks.
- Resolved the open cache invalidation conflicts by tagging navigation, product, and dashboard caches with the shared helper, wiring the invalidation service into global model events, and adding regression tests that prove storefront widgets and stats refresh immediately after catalogue edits.
