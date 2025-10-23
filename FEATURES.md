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
- Added a cache invalidation service with locale-aware tag helpers alongside new storefront regression tests so the home widgets, dashboards, and cart/checkout experiences stay consistent release over release.
