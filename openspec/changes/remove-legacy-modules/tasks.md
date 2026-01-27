## 1. Implementation
- [ ] 1.1 Inventory references to each removal target using g and map dependencies.
- [ ] 1.2 Remove the legacy models and their relation methods from remaining models.
- [ ] 1.3 Remove legacy directories/files (observers, OpenAPI, use cases, view/application layers, logging helpers, factories, domain, data transfer, application).
- [ ] 1.4 Remove or refactor container bindings, service providers, config entries, and route/test references that depend on removed classes.
- [ ] 1.5 Clean up recommendation-related references that depend on removed classes or relations.
- [ ] 1.6 Run the test suite and static analysis; fix regressions caused by removals.
- [ ] 1.7 Re-scan for removed symbols with g and ensure no stale references remain.