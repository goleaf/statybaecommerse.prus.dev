# Remove Project Domain

## Why
The Project domain is legacy in this repository and the request is to fully remove it, starting from pp/Models/Project.php and all dependent code, schema, factories, and tests.

## What Changes
- Remove the Project model and any project-specific traits/services.
- Remove project-related morph map entries and comment optimization mappings.
- Remove the projects table and project_user pivot tables from migrations.
- Replace task → project relationships with task → organization relationships.
- Remove project factories and project-centric relationship tests.

## Impact
- Breaking change for any code or data relying on projects or project_id.
- Tasks will now be attached directly to organizations via organization_id.
- Running migrations from scratch will no longer create projects or project_user.