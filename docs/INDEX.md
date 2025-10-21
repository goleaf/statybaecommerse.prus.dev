# Documentation Index

A curated map of the most useful documents for day-to-day development and operations. Start here after reading the project README.

## Curated Path

### 1. Set Up Your Environment
- [ARCHITECTURE_OVERVIEW](ARCHITECTURE_OVERVIEW.md) – orient yourself across backend, storefront, and automation systems.
- [IMPLEMENTATION_STATUS](IMPLEMENTATION_STATUS.md) – confirm which subsystems are ready before provisioning services.
- [AUTOFIX_SETUP](AUTOFIX_SETUP.md) & [REALTIME_AUTOFIX_GUIDE](REALTIME_AUTOFIX_GUIDE.md) – install the optional automation helpers that speed up local workflows.

### 2. Prepare for Deployment
- [DEPLOYMENT_GUIDE](DEPLOYMENT_GUIDE.md) – infrastructure prerequisites, environment variables, and queue/cache expectations.
- [DEPLOYMENT_READINESS_CHECKLIST](DEPLOYMENT_READINESS_CHECKLIST.md) – preflight checks to validate staging and production readiness.
- [PRODUCTION_DEPLOYMENT_CHECKLIST](PRODUCTION_DEPLOYMENT_CHECKLIST.md) – runbook for final launch sequencing and verification.

### 3. Understand the Data Model
- [MIGRATION_SUMMARY](MIGRATION_SUMMARY.md) – history of schema changes and domain-specific data notes.
- [PRODUCT_VARIANTS_IMPLEMENTATION_SUMMARY](PRODUCT_VARIANTS_IMPLEMENTATION_SUMMARY.md) & [PRODUCT_VARIANTS_SUMMARY](PRODUCT_VARIANTS_SUMMARY.md) – catalogue relationships, pricing logic, and attribute usage.
- [RECOMMENDATION_SYSTEM_IMPLEMENTATION](RECOMMENDATION_SYSTEM_IMPLEMENTATION.md) – outlines data flows powering personalised suggestions.

### 4. Master the Admin Experience
- [FILAMENT_V4_IMPLEMENTATION_SUMMARY](analysis/FILAMENT_V4_IMPLEMENTATION_SUMMARY.md) – component patterns and conventions for building admin UI.
- [NEWS_RESOURCE_IMPLEMENTATION_SUMMARY](analysis/NEWS_RESOURCE_IMPLEMENTATION_SUMMARY.md) & [COMPANY_RESOURCE_ANALYSIS](analysis/COMPANY_RESOURCE_ANALYSIS.md) – worked examples of resources and permissions in action.
- [Combobox field reference](forms/COMBOBOX.md) – where the dual-list picker is enabled, configuration knobs, and localisation tips.
- [Searchable input metadata lifecycle](forms/SEARCHABLE_INPUT_METADATA.md) – the canonical `SearchResult` payload, helper APIs, and how forms hydrate dependent fields.
- [Matrix choice permission grids](forms/MATRIX_CHOICE.md) – which Filament resources consult the permission matrix, how rows/columns are mapped, and how to extend the config safely.
- [REFERRAL_SYSTEM_IMPLEMENTATION](REFERRAL_SYSTEM_IMPLEMENTATION.md) – loyalty and referral programme operations for support teams.
- [Table Column Resizing](ui/RESIZED_COLUMNS.md) – how column width adjustments are saved, reset, and recovered when troubleshooting.
- [Admin Translations Guide](analysis/FILAMENT_V4_IMPLEMENTATION_SUMMARY.md#spatie-translatable) – covers plugin registration, persisted locale behaviour, and required traits for locale-aware resources/pages.

### 5. Troubleshoot & Maintain
- [TERMINAL_FREEZING_FIXES](TERMINAL_FREEZING_FIXES.md) – remedies for common local environment hiccups.
- [Cache Policy](CachePolicy.md) – cache key conventions, TTLs, and when to refresh derived data.
- [CURRENT_SYSTEM_STATUS](CURRENT_SYSTEM_STATUS.md) – snapshot of live issues, mitigations, and follow-up owners.

## Explore Further
- [Analysis & Summary Index](analysis/INDEX.md) – master index to deep-dive research, audits, and rollout recaps.
- [PROJECT_HANDOVER_DOCUMENTATION](PROJECT_HANDOVER_DOCUMENTATION.md) – business context and administrative handover notes.
- [COMPLETE_PROJECT_ARCHIVE_INDEX](COMPLETE_PROJECT_ARCHIVE_INDEX.md) – historical artefacts for auditors and new maintainers.
- [CHANGELOG](CHANGELOG.md) – high-level log of significant migrations and releases.
- [PERFORMANCE_REPORT](PERFORMANCE_REPORT.md) – benchmarks and performance tuning recommendations.
- [tests/README](../tests/README.md) – tour of automated test coverage for backend and UI.
- [Public Collections API schema](openapi/collections.public.yaml) – contract exercised by automated OpenAPI validation for storefront collection endpoints.

Need another guide? Search the `docs/` directory for keywords or open a summary above to continue exploring the knowledge base.
