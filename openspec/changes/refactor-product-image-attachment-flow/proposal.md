## Why
Product image upload/attach writes are currently split across Filament relation managers, resource pages, and importer-specific logic. This causes inconsistent attach behavior (ownership reassignment instead of clone), duplicated path handling, and unsafe/uneven file cleanup.

## What Changes
- Add a shared product image write service to centralize create, update, clone-attach, append, replace, and delete flows.
- Refactor product Filament images relation manager to clone existing images when attaching instead of reassigning ownership.
- Refactor product image create/edit/delete paths in ProductImageResource to use the shared service.
- Refactor product importer image flows to use hybrid behavior with shared write logic:
  - `image_url`: replace current product images.
  - `image`: append additional product image paths.
- Add shared-path-safe file cleanup for replaced/removed product images.
- Mirror product image writes to legacy product media collection for compatibility with existing read paths.

## Impact
- Product image write behavior is consistent across admin and importer flows.
- Existing products retain compatibility where `getFirstMediaUrl('images')` is still used.
- Attach behavior is now non-destructive (clone), preventing cross-product ownership moves.
