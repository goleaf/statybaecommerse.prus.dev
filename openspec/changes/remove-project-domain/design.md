# Design: Remove Project Domain

## Summary
This change removes the entire Project domain and rewires the remaining task relationships to be organization-scoped. The simplest viable replacement is to attach tasks directly to organizations.

## Key Decisions
- Tasks now reference organizations directly via organization_id.
- Organization relationships become direct hasMany(Task::class) rather than hasManyThrough via projects.
- Project-specific relationship traits and services that are unused are removed to avoid stale references.

## Schema Updates
- Drop the projects table definition from the base migrations.
- Remove project_user pivots from base and fallback pivot migrations.
- Update 	asks table migration to use organization_id instead of project_id.

## Code Updates
- Remove App\Models\Project and Database\Factories\ProjectFactory.
- Update Task, Organization, Tag, and RelationshipServiceProvider to remove project references.
- Update comment optimization to avoid morph loading projects.

## Risks / Follow-ups
- Existing databases will require manual migration steps to drop projects and move project_id to organization_id.
- Any external integrations that expect projects will break and must be updated.