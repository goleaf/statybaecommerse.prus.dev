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
- Resolved the open cache invalidation conflicts by tagging navigation, product, and dashboard caches with the shared helper, wiring the invalidation service into global model events, and adding regression tests that prove storefront widgets and stats refresh immediately after catalogue edits.
