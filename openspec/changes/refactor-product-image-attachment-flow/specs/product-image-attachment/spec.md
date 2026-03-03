## ADDED Requirements
### Requirement: Product Image Writes Must Use a Unified Service
The system MUST use a single shared write service for product image create, update, attach, append, replace, and delete operations.

#### Scenario: Creating a product image from admin UI
- **WHEN** an admin creates a product image from product relation manager or product image resource
- **THEN** the image record is persisted via the shared write service
- **AND** sort/default behavior is applied consistently

#### Scenario: Updating a product image with a new uploaded file
- **WHEN** an admin updates an existing product image with a new file
- **THEN** the image record path is updated via the shared write service
- **AND** the previous file is removed only when no other product image references that path

### Requirement: Product Image Attach Must Clone, Not Reassign
The system MUST clone attached product images so the source image keeps its original product ownership.

#### Scenario: Attach existing image to another product
- **GIVEN** product A has an existing product image
- **WHEN** an admin attaches that image to product B
- **THEN** a new product image record is created for product B with the same path
- **AND** the original record for product A remains unchanged

### Requirement: Product Import Image Handling Must Support Hybrid Replace and Append
The importer MUST support replacing by `image_url` and appending by `image` within the same row lifecycle.

#### Scenario: image_url replaces product images
- **WHEN** a row includes `image_url`
- **THEN** existing product images are removed and replaced with a single default imported image

#### Scenario: image appends product images
- **WHEN** a row includes `image`
- **THEN** provided image paths are appended without removing existing product images

#### Scenario: image_url and image are both provided
- **WHEN** a row includes both `image_url` and `image`
- **THEN** replacement from `image_url` happens first
- **AND** additional image paths from `image` are appended after replacement

### Requirement: Legacy Product Media Compatibility Must Be Maintained During Transition
The system MUST keep legacy product media reads compatible while product image writes move to `product_images`.

#### Scenario: Product image write triggers media mirror refresh
- **WHEN** product images are created, updated, attached, replaced, or deleted
- **THEN** the compatibility media collection for product images is refreshed to represent the current primary image state
