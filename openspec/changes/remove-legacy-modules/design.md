## Context
The requested change is a broad, breaking removal across several architectural layers and models. The primary risk is leaving dangling references in providers, config, routes, or tests that will cause autoloading or container resolution errors.

## Goals / Non-Goals
- Goals:
  - Remove the specified models and directories.
  - Eliminate Eloquent relations and container bindings that reference removed classes.
  - Leave the application in a bootable state with passing tests where possible.
- Non-Goals:
  - Replacing removed behavior with new features.
  - Redesigning architecture beyond necessary cleanup.

## Decisions
- Decision: Treat this as a removal/refactor change and centralize it under a single capability spec (legacy-modules).
- Decision: Remove references first (relations/bindings/tests), then delete files to minimize intermediate breakage.
- Alternatives considered: Split into multiple changes. Rejected for now to match the single explicit removal request.

## Risks / Trade-offs
- Risk: Removing pp/View and recommendation code may break frontend and admin UI features.
  - Mitigation: Inventory usages and either delete dependent code paths or provide minimal fallbacks.
- Risk: Removing domain/application layers may require refactoring repositories and providers.
  - Mitigation: Inventory references and update bindings to concrete infrastructure implementations.

## Migration Plan
1. Inventory references.
2. Remove relations and references in existing code.
3. Delete files/directories.
4. Run tests and static analysis.
5. Sweep for stale references.

## Open Questions
- Scope clarification needed: "remove all code files and relations with recommendation" could imply removing recommendation models, resources, seeders, feature flags, views, and admin navigation.