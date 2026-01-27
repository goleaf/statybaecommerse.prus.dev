# Change: Remove review model and related features

## Why
The application references `App\\Models\\Review`, which does not exist. We want to remove all review-related code and dependencies entirely.

## What Changes
- Remove all code paths referencing Review models, resources, relations, and views.
- Remove review-related tests, routes, and UI elements.
- Ensure the application runs without review features or dependencies.

## Impact
- Affected specs: reviews
- Affected code: models, Filament resources, controllers, routes, tests, views
