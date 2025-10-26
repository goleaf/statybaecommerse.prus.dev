# Documentation Index

Use this page to discover the project's internal documentation. The content is organised into three domains—analysis, runbooks, and contracts—so you can jump to implementation history, operational procedures, or formal deliverables without combing through the repository tree.

## Directory overview
- [Analysis](analysis/) – research notes, implementation summaries, health reports, and historical memory-bank artefacts.
- [Runbooks](runbooks/) – actionable guides, checklists, and supporting assets for repeatable operational tasks.
- [Contracts](contracts/) – formal project deliverables, closure packets, and partner-facing specifications.
- [Forms](forms/) – reusable field patterns and UI component references for Filament resources.
- [UI](ui/) – visual behaviour guides, including column resizing rules and design tokens.

### 1. Set Up Your Environment
- [ARCHITECTURE_OVERVIEW](ARCHITECTURE_OVERVIEW.md) – orient yourself across backend, storefront, and automation systems.
- [IMPLEMENTATION_STATUS](analysis/IMPLEMENTATION_STATUS.md) – confirm which subsystems are ready before provisioning services.
- [AUTOFIX_SETUP](runbooks/AUTOFIX_SETUP.md) & [REALTIME_AUTOFIX_GUIDE](runbooks/REALTIME_AUTOFIX_GUIDE.md) – install the optional automation helpers that speed up local workflows.
- [Documentation style guide](STYLE_GUIDE.md) – conventions for naming, linking, and structuring future contributions.

### 2. Prepare for Deployment
- [DEPLOYMENT_GUIDE](runbooks/DEPLOYMENT_GUIDE.md) – infrastructure prerequisites, environment variables, and queue/cache expectations.
- [DEPLOYMENT_READINESS_CHECKLIST](runbooks/DEPLOYMENT_READINESS_CHECKLIST.md) – preflight checks to validate staging and production readiness.
- [PRODUCTION_DEPLOYMENT_CHECKLIST](runbooks/PRODUCTION_DEPLOYMENT_CHECKLIST.md) – runbook for final launch sequencing and verification.

### 3. Understand the Data Model
- [MIGRATION_SUMMARY](analysis/MIGRATION_SUMMARY.md) – history of schema changes and domain-specific data notes.
- [PRODUCT_VARIANTS_IMPLEMENTATION_SUMMARY](analysis/PRODUCT_VARIANTS_IMPLEMENTATION_SUMMARY.md) & [PRODUCT_VARIANTS_SUMMARY](analysis/PRODUCT_VARIANTS_SUMMARY.md) – catalogue relationships, pricing logic, and attribute usage.
- [RECOMMENDATION_SYSTEM_IMPLEMENTATION](analysis/RECOMMENDATION_SYSTEM_IMPLEMENTATION.md) – outlines data flows powering personalised suggestions.

### 4. Master the Admin Experience
- [FILAMENT_V4_IMPLEMENTATION_SUMMARY](analysis/FILAMENT_V4_IMPLEMENTATION_SUMMARY.md) – component patterns and conventions for building admin UI.
- [NEWS_RESOURCE_IMPLEMENTATION_SUMMARY](analysis/NEWS_RESOURCE_IMPLEMENTATION_SUMMARY.md) & [COMPANY_RESOURCE_ANALYSIS](analysis/COMPANY_RESOURCE_ANALYSIS.md) – worked examples of resources and permissions in action.
- [Combobox field reference](forms/COMBOBOX.md) – where the dual-list picker is enabled, configuration knobs, and localisation tips.
- [Searchable input metadata lifecycle](forms/SEARCHABLE_INPUT_METADATA.md) – the canonical `SearchResult` payload, helper APIs, and how forms hydrate dependent fields.
- [Matrix choice permission grids](forms/MATRIX_CHOICE.md) – which Filament resources consult the permission matrix, how rows/columns are mapped, and how to extend the config safely.
- [REFERRAL_SYSTEM_IMPLEMENTATION](analysis/REFERRAL_SYSTEM_IMPLEMENTATION.md) – loyalty and referral programme operations for support teams.
- [Table Column Resizing](ui/RESIZED_COLUMNS.md) – how column width adjustments are saved, reset, and recovered when troubleshooting.
- [Admin Translations Guide](analysis/FILAMENT_V4_IMPLEMENTATION_SUMMARY.md#spatie-translatable) – covers plugin registration, persisted locale behaviour, and required traits for locale-aware resources/pages.

### 5. Troubleshoot & Maintain
- [TERMINAL_FREEZING_FIXES](runbooks/TERMINAL_FREEZING_FIXES.md) – remedies for common local environment hiccups.
- [Cache Policy](runbooks/CachePolicy.md) – cache key conventions, TTLs, and when to refresh derived data.
- [Collection Timeout Macros](runbooks/TIMEOUT_COLLECTION_MACROS.md) – explains how the shared `takeUntilTimeout` helper guards long running loops.
- [CURRENT_SYSTEM_STATUS](analysis/CURRENT_SYSTEM_STATUS.md) – snapshot of live issues, mitigations, and follow-up owners.
- [Dependency automation schedule](operations/RENOVATE_OVERVIEW.md) – explains the Renovate rollup cadence and CI expectations.
- [PR Branch Cleanup Workflow](../.github/workflows/pr-branch-cleanup.yml) – documents the automation that deletes local branches when pull requests close without merging; the job now ignores already-removed branches after validating the reference.
- **Testing shortcut** – run `composer test` to invoke the bundled Pest runner (`vendor/bin/pest`) without requiring a global installation.

## Explore Further
- [Analysis & Summary Index](analysis/INDEX.md) – master index to deep-dive research, audits, and rollout recaps.
- [PROJECT_HANDOVER_DOCUMENTATION](analysis/PROJECT_HANDOVER_DOCUMENTATION.md) – business context and administrative handover notes.
- [COMPLETE_PROJECT_ARCHIVE_INDEX](analysis/COMPLETE_PROJECT_ARCHIVE_INDEX.md) – historical artefacts for auditors and new maintainers.
- [CHANGELOG](analysis/CHANGELOG.md) – high-level log of significant migrations and releases.
- [PERFORMANCE_REPORT](analysis/PERFORMANCE_REPORT.md) – benchmarks and performance tuning recommendations.
- [tests/README](../tests/README.md) – tour of automated test coverage for backend and UI.
- [Public Collections API schema](openapi/collections.public.yaml) – contract exercised by automated OpenAPI validation for storefront collection endpoints.

Need another guide? Search the `docs/` directory for keywords or open a summary above to continue exploring the knowledge base.
