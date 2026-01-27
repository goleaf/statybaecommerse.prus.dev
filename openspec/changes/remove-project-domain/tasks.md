# Tasks

1. Remove the Project model and project-specific unused traits/services.
2. Update app code to remove Project references (morph maps, relationships, mappings).
3. Update migrations to remove projects/project_user and replace project_id with organization_id on tasks.
4. Update factories to align with the new task schema.
5. Remove project-centric relationship tests and any failing references.
6. Validate with openspec validate remove-project-domain --strict.
7. Run composer run test and composer run analyze and fix any remaining failures.