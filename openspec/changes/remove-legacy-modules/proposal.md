# Change: Remove Legacy/Unused Modules and Relations

## Why
The codebase still contains legacy modules, models, observers, and architectural layers that are no longer desired. They add maintenance overhead and create fragile cross-references.

## What Changes
- Remove the listed legacy models and any Eloquent relations that reference them.
- Remove entire legacy directories and their references (observers, OpenAPI, use cases, view models, application override, logging helpers, factories, domain, data transfer, and application layers).
- Remove recommendation-related legacy code and relations where it depends on the removed components.
- Update references in providers, controllers, services, config, seeders, routes, and tests to avoid runtime errors.
- Add targeted cleanup to ensure autoloading and container bindings no longer reference removed classes.
- **BREAKING**: Any code paths, tests, or admin UI that depend on these removed modules will be deleted or simplified.

## Impact
- Affected specs: legacy-modules
- Affected code:
  - pp/Models/* (listed models)
  - pp/Observers/*
  - pp/OpenApi/* and OpenAPI attribute usage
  - pp/UseCases/*
  - pp/View* (components/view models)
  - pp/ApplicationOverride.php
  - pp/Logging/*
  - pp/Factories/*
  - pp/Domain/*
  - pp/DataTransfer/*
  - pp/Application/*
  - Any referencing providers, services, seeders, routes, and tests