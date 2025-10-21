# Documentation Style Guide

The knowledge base now lives inside dedicated collections so contributors can find runbooks, contracts, and analytical reports in
predictable places. Use this guide whenever you add, rename, or link documentation.

## Directory layout
- `docs/analysis/` – long-form audits, summaries, and retrospective documents that explain _why_ we made decisions.
- `docs/runbooks/` – actionable, step-by-step instructions for operations, tooling, or troubleshooting tasks.
- `docs/contracts/` – API contracts, data exchange formats, and sample payloads shared with other teams or integrations.
- `docs/filament/` – deep dives that focus on Filament navigation, widgets, or resource-specific ergonomics.
- `docs/forms/`, `docs/ui/`, `docs/operations/` – existing topical directories continue to group smaller reference notes.

## Naming conventions
- Favour descriptive uppercase file names such as `PROJECT_COMPLETION_SUMMARY.md` for legacy artefacts that already ship with that
  convention.
- For new documents prefer `kebab-case` that matches the directory focus, e.g. `queue-drain-runbook.md` inside `docs/runbooks/`.
- Prefix runbooks with verbs that describe the outcome (`reset-search-index.md`) and analyses with the domain they cover (`catalog-
  attribution-retrospective.md`).

## Linking rules
- When referencing documents inside the same directory use relative links (`[Cache Policy](CachePolicy.md)`).
- When linking across directories, include the folder name (`[Deployment Guide](runbooks/DEPLOYMENT_GUIDE.md)`).
- Update both the main [documentation index](INDEX.md) and the relevant sub-index (for example `analysis/INDEX.md`) after moving or
  creating a document.

## Contribution checklist
1. Confirm the document sits in the correct directory based on the guidance above.
2. Add a short summary sentence at the top describing the intent of the document.
3. Cross-link related runbooks or analyses when it improves discoverability.
4. Update `README.md`, `CHANGELOG.md`, and `FEATURES.md` when the documentation introduces new capabilities or processes.
5. Keep Markdown under 2 MB so it passes the documentation size guard introduced in CI.
