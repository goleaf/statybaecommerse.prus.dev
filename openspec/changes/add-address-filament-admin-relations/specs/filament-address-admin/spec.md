## ADDED Requirements

### Requirement: Admin Address CRUD
The admin panel SHALL provide a dedicated Address resource that supports creating, viewing, editing, and deleting address records.

#### Scenario: Address index renders
- **WHEN** an admin visits `/admin/addresses`
- **THEN** the address list renders without runtime type errors

#### Scenario: Address can be created with relations
- **WHEN** an admin creates an address and selects a user, country, and city
- **THEN** the address is saved and those relations resolve on subsequent views

### Requirement: Address Relations Are Manageable From Related Records
The admin panel SHALL allow admins to manage addresses from related records that have authoritative address relations.

#### Scenario: Manage addresses from a user
- **WHEN** an admin views a user record
- **THEN** they can list and manage the user's addresses from a relation manager

#### Scenario: Inspect addresses from a country or city
- **WHEN** an admin views a country or city record
- **THEN** they can list related addresses without relation errors