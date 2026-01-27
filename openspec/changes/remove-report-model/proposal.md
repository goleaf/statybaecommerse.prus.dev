# Change: Remove Report Model and Report Surface Area

## Why
The Report model and its report pages/routes are no longer desired. Keeping them increases maintenance cost and creates failing references across the codebase.

## What Changes
- Remove the App\\Models\\Report model and supporting factory/seeder.
- Remove report-facing web routes and the report controller.
- Remove report Blade views that depend on the model.
- Remove report-related tests that reference the model or report resources.
- Remove the reports database table via a new migration.

## Impact
- Affected specs: reports (new capability delta for removal)
- Affected code: pp/Models/Report.php, pp/Http/Controllers/ReportController.php, outes/reports.php, esources/views/reports/*, database/factories/ReportFactory.php, database/seeders/ReportSeeder.php, report-related tests, and a new migration to drop eports.