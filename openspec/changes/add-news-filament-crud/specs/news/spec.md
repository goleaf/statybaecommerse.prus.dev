## ADDED Requirements

### Requirement: News Admin CRUD
Admins MUST be able to list, create, edit, and view News entries via Filament.

#### Scenario: List news
- **WHEN** an admin opens the News resource index
- **THEN** existing News records are listed with key fields (title, status, published date)

#### Scenario: Create news
- **WHEN** an admin creates a News entry with required fields
- **THEN** the News entry is persisted and visible in the list

#### Scenario: Edit news
- **WHEN** an admin updates a News entry
- **THEN** the changes are saved and reflected in the list
