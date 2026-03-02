## ADDED Requirements

### Requirement: Users Can Edit Existing Account Addresses
The account addresses page SHALL allow authenticated users to edit their existing saved addresses.

#### Scenario: Start editing an existing address
- **WHEN** a user clicks edit on one of their saved addresses on `/account/addresses`
- **THEN** the address form is populated with that address data
- **AND** the page enters edit mode for that specific address

#### Scenario: Save edited address
- **WHEN** a user in edit mode submits valid address changes
- **THEN** the selected existing address record is updated
- **AND** the user sees a success notification
- **AND** the updated values are reflected in the address list

#### Scenario: Cancel edit
- **WHEN** a user cancels edit mode
- **THEN** no address data is changed
- **AND** the form returns to create mode with a cleared state

### Requirement: Address Editing Preserves Security and Validation
Address edits SHALL enforce the same validation and authorization guarantees as address creation.

#### Scenario: Prevent editing another user's address
- **WHEN** a user attempts to edit an address they do not own
- **THEN** the request is rejected
- **AND** no changes are persisted

#### Scenario: Validate edited values before update
- **WHEN** a user submits edited address data
- **THEN** the same field validation rules used for create are applied
- **AND** invalid data is not saved
