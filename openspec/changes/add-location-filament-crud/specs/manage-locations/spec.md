## ADDED Requirements
### Requirement: Location Admin CRUD
The system MUST provide a Filament resource that lets administrators list, create, view, and edit locations.

#### Scenario: Admin views locations list
- **WHEN** an admin opens the Locations resource
- **THEN** existing location records are listed with key fields

### Requirement: General Tab for Business Fields
The system MUST present non-translatable business fields for a location in a General tab (code, type, address, contact, hours, coordinates, status).

#### Scenario: Admin edits general fields
- **WHEN** an admin edits a location
- **THEN** they can update business fields in the General tab

### Requirement: Locale Tabs for Translatable Fields
The system MUST present per-locale tabs for the translatable fields (name, slug, description) and persist them to the location translations table.

#### Scenario: Admin saves multiple locales
- **WHEN** an admin submits translations for multiple locales
- **THEN** the translations are stored per locale and available for display

### Requirement: Default Locale Sync
The system MUST keep the base locations columns in sync with the configured default locale values on save.

#### Scenario: Default locale updates base fields
- **WHEN** the default locale fields are saved
- **THEN** the base locations columns reflect those values
