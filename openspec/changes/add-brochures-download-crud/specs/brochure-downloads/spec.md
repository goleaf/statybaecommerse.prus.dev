## ADDED Requirements
### Requirement: Brochure Admin CRUD
The system MUST provide a Filament Brochure resource in the Content navigation group that allows administrators to create, edit, list, and delete brochures.

#### Scenario: Admin creates brochure
- **WHEN** an admin submits brochure details in the admin panel
- **THEN** the brochure is persisted and appears in the brochures list.

### Requirement: Multiple PDF Files Per Brochure
The system MUST allow each brochure to have multiple downloadable PDF files managed inline within the brochure form.

#### Scenario: Admin adds two brochure files
- **WHEN** an admin adds two file rows with names and PDF uploads
- **THEN** both files are stored and linked to the brochure.

### Requirement: Activation Guard For Brochures
The system MUST prevent saving an active brochure unless it has at least one active file with a stored path.

#### Scenario: Active brochure without active files
- **WHEN** an admin saves a brochure with `is_active = true` and no active files
- **THEN** validation fails with an error on the files field.

### Requirement: Public Localized Brochure Downloads
The system MUST expose brochure downloads on a localized storefront page and include a non-localized redirect.

#### Scenario: Localized brochures page
- **WHEN** a visitor opens `/{locale}/brochures`
- **THEN** only active brochure files from active brochures are listed.

#### Scenario: Non-localized redirect
- **WHEN** a visitor opens `/brochures`
- **THEN** they are redirected to `/{locale}/brochures` using the active application locale.

### Requirement: Signed Download URLs
The system MUST generate signed secure-media URLs for brochure files without expiration.

#### Scenario: Brochure download URL
- **WHEN** a download link is rendered for a brochure file
- **THEN** the URL includes a valid signature and download parameter and does not include an expiration timestamp.
