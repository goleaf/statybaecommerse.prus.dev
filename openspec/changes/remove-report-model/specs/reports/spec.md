## REMOVED Requirements
### Requirement: Report Catalog and Detail Pages
The system SHALL provide public report catalog and detail pages backed by the Report model.

#### Scenario: Reports are removed from the storefront
- **WHEN** a user attempts to access report catalog or detail routes
- **THEN** those routes are no longer registered
- **AND** no Report model references remain in the application code