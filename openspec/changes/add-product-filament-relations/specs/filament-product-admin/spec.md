## ADDED Requirements

### Requirement: Product Admin Relations Are Manageable
The admin panel SHALL allow managing core product relations from the product record.

#### Scenario: Manage product variants from product view/edit
- **WHEN** an admin opens a product in Filament
- **THEN** they can list, create, edit, and delete related variants

#### Scenario: Manage product images from product view/edit
- **WHEN** an admin opens a product in Filament
- **THEN** they can list, create, edit, and delete related images

#### Scenario: Manage product features from product view/edit
- **WHEN** an admin opens a product in Filament
- **THEN** they can list, create, edit, and delete related features

#### Scenario: Manage product requests, comparisons, and similarities
- **WHEN** an admin opens a product in Filament
- **THEN** they can list related requests, comparisons, and similarities

### Requirement: Product Resources Support CRUD and Filtering
The admin panel SHALL expose dedicated Product* resources that support CRUD and filters used by existing tests.

#### Scenario: List pages mount without errors
- **WHEN** Filament list pages for Product* resources are mounted
- **THEN** they render table records without runtime errors

#### Scenario: Stock filters behave deterministically
- **WHEN** admins filter product variants by stock status
- **THEN** results reflect available stock derived from stock and reserved quantities when inventory rows are absent