# Documentation Index

Use this page to discover the project's internal documentation. The content is organised into three domains—analysis, runbooks, and contracts—so you can jump to implementation history, operational procedures, or formal deliverables without combing through the repository tree.

## Directory overview
- [Analysis](analysis/) – research notes, implementation summaries, health reports, and historical memory-bank artefacts.
- [Runbooks](runbooks/) – actionable guides, checklists, and supporting assets for repeatable operational tasks.
- [Contracts](contracts/) – formal project deliverables, closure packets, and partner-facing specifications.
- [Forms](forms/) – reusable field patterns and UI component references for Filament resources.
- [UI](ui/) – visual behaviour guides, including column resizing rules and design tokens.

## Analysis highlights
### Implementation summaries & deep dives
- [Filament v4 implementation summary](analysis/FILAMENT_V4_IMPLEMENTATION_SUMMARY.md) – schema-based resource conventions and examples.
- [News resource implementation summary](analysis/NEWS_RESOURCE_IMPLEMENTATION_SUMMARY.md) – editorial workflows, moderation, and translations.
- [Company resource analysis](analysis/COMPANY_RESOURCE_ANALYSIS.md) – permissions, navigation, and localisation structure for company management.
- [Components analysis summary](analysis/COMPONENTS_ANALYSIS_SUMMARY.md) – reusable Livewire, Filament, and Blade building blocks.
- [Seeder factory conversion summary](analysis/SEEDER_FACTORY_CONVERSION_SUMMARY.md) – rationale behind factory-first seeding.

### Health & status snapshots
- [Current system status](CURRENT_SYSTEM_STATUS.md) – active issues, mitigations, and follow-up owners.
- [Implementation status](IMPLEMENTATION_STATUS.md) – subsystem readiness checklist.
- [Performance report](PERFORMANCE_REPORT.md) – benchmarks and tuning recommendations.
- [Migration summary](MIGRATION_SUMMARY.md) – schema evolution timeline and domain notes.

### Tools & automation
- [Architecture overview](ARCHITECTURE_OVERVIEW.md) – backend, storefront, and automation topology.
- [Cache policy](CachePolicy.md) – cache key conventions and refresh strategy.
- [Autofix setup](AUTOFIX_SETUP.md) & [Realtime autofix guide](REALTIME_AUTOFIX_GUIDE.md) – local repair tooling and workflows.
- [Comprehensive system health report](COMPREHENSIVE_SYSTEM_HEALTH_REPORT.md) – aggregated diagnostics and remediation plans.

## Runbook essentials
- [Deployment guide](DEPLOYMENT_GUIDE.md) – infrastructure prerequisites and environment preparation.
- [Deployment readiness checklist](DEPLOYMENT_READINESS_CHECKLIST.md) – staging and production preflight validation.
- [Production deployment checklist](PRODUCTION_DEPLOYMENT_CHECKLIST.md) – go-live sequencing and verification.
- [Backup and restore](runbooks/BACKUP_RESTORE.md) – recovery procedures for the primary data stores.
- [Queue operations](runbooks/QUEUES.md) – Horizon, worker scaling, and failure handling.
- [Database indexing](runbooks/DB_INDEXING.md) – index audits and maintenance cadence.
- [Media pipeline](runbooks/MEDIA.md) – storage, conversions, and cleanup policies.
- [Security playbook](runbooks/SECURITY.md) – incident response and secrets management expectations.
- [Terminal freezing fixes](TERMINAL_FREEZING_FIXES.md) – remedies for local environment hiccups.

## Contracts & closure artefacts
- [Project gospel document](contracts/PROJECT_GOSPEL_DOCUMENT.md) – executive summary of business goals and outcomes.
- [Project handover documentation](contracts/PROJECT_HANDOVER_DOCUMENTATION.md) – administrative context for new maintainers.
- [Project testament document](contracts/PROJECT_TESTAMENT_DOCUMENT.md) – legal close-out package.
- [Partner API contract](contracts/PARTNER_API.md) – storefront integration schema.
- [Notifications API contract](contracts/NOTIFICATIONS_API.md) – messaging payload shapes and retry semantics.
- [Routes contract](contracts/ROUTES.md) – canonical route inventory for storefront and admin entry points.
- [Error catalogue](contracts/ERRORS.md) & [error codes reference](contracts/ERROR_CODES.md) – shared failure modes.
- [Authorization matrix](contracts/AUTHORIZATION.md) – role/permission commitments.
- [User profile dataset sample (CSV)](contracts/user_profiles.sample.csv) & [JSON variant](contracts/user_profiles.sample.json) – reference payloads for analytics importers.

## Contribution checklist
- Review the [Documentation Style Guide](CONTRIBUTING_DOCS.md) before adding or updating any files in this directory.
- After publishing a new document, update this index and, if applicable, the [analysis index](analysis/INDEX.md) or relevant sub-index so other contributors can discover the content quickly.
- Keep each committed file under 2MB so the automated documentation size check succeeds.
