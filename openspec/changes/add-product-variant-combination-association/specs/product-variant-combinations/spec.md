## ADDED Requirements
### Requirement: Associate Variant Combinations to Product
The system MUST allow admins to associate an existing Variant Combination to a Product from the Product edit page.

#### Scenario: Admin associates an existing combination
- **GIVEN** a Variant Combination exists without an assigned product
- **WHEN** an admin uses the Associate action in the Product → Variant Combinations relation manager
- **THEN** the Variant Combination is assigned to the current product
- **AND** it appears in the product’s Variant Combinations list

### Requirement: Only Unassigned Combinations Are Offered
The system MUST only list Variant Combinations with no assigned product in the Associate selection list.

#### Scenario: Combination already assigned to another product
- **GIVEN** a Variant Combination belongs to a different product
- **WHEN** an admin opens the Associate action list
- **THEN** that Variant Combination is not listed
