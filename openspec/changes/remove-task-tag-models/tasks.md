## 1. Discovery & Safety Checks
- [ ] 1.1 Enumerate all references to `Task`, `Tag`, and `Taggable` in `app/`, `database/`, `routes/`, `resources/`, and `tests/` using `rg`.
- [ ] 1.2 Review relationship concerns and providers to identify indirect references and query-time dependencies.

## 2. Remove Models and Direct Dependencies
- [ ] 2.1 Delete `app/Models/Task.php`, `app/Models/Tag.php`, and `app/Models/Taggable.php`.
- [ ] 2.2 Remove task/tag-specific factories and pivot models that reference the deleted models.

## 3. Refactor Indirect Dependencies
- [ ] 3.1 Update model concerns (e.g., optimized/custom/conditional relationships) to remove task/tag-specific relationships, eager loading, and subqueries.
- [ ] 3.2 Update services (e.g., relationship query services, optimization services, and providers) to eliminate task/tag queries and lookups.
- [ ] 3.3 Update model relationships (e.g., `User`, `Project`, `Organization`, `Comment`, `File`) to remove task/tag relationships.

## 4. Tests and Validation
- [ ] 4.1 Remove or refactor tests that depend on `Task`, `Tag`, and `Taggable` models.
- [ ] 4.2 Run `composer run test` and address regressions.
- [ ] 4.3 Run `composer run analyze` and fix any static analysis failures related to removals.