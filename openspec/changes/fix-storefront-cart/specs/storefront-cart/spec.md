## ADDED Requirements
### Requirement: Storefront add-to-cart action
The system SHALL allow a storefront user to add a product to the cart from the home page, including selecting a product variant.

#### Scenario: Add base product to cart
- **WHEN** a user triggers add-to-cart for a product without a variant
- **THEN** the cart SHALL contain the product with the requested quantity

#### Scenario: Add variant product to cart
- **WHEN** a user triggers add-to-cart for a product variant
- **THEN** the cart SHALL contain the variant with the requested quantity

#### Scenario: Add-to-cart enforces availability
- **WHEN** a user attempts to add a product or variant that is unavailable
- **THEN** the system SHALL reject the request and show a user-visible warning

### Requirement: Storefront cart page rendering
The system SHALL render the storefront cart page with the current cart items and totals.

#### Scenario: Cart page shows items after add-to-cart
- **WHEN** a user adds an item to the cart
- **THEN** visiting the cart page SHALL display the item and updated totals
