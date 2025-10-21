# Relation Manager Repeater quick-edit actions

The `zvizvi/relation-manager-repeater` plugin (v2.x) is installed to provide bulk editing modals for high-volume HasMany relations. Each enabled relation manager now exposes a **Quick edit** header action that opens a repeater-powered modal and syncs the submitted rows back to the underlying relationship.

## Enabled relation managers

| Relation manager | Action label | Repeater highlights |
| --- | --- | --- |
| `ProductResource\RelationManagers\ImagesRelationManager` | **Quick edit images** | Reuses the image upload flow plus metadata fields, supports cloning/reordering, and keeps existing records stable through a hidden `id` input. |
| `ProductResource\RelationManagers\VariantsRelationManager` | **Quick edit variants** | Focuses on pricing, inventory, and publish toggles; variant attributes stay editable in the standard form. |
| `OrderResource\RelationManagers\OrderItemsRelationManager` | **Quick edit items** | Lets operators adjust quantity, pricing, and notes without reopening each row; product and SKU stay read-only safeguards. |
| `UserResource\RelationManagers\AddressesRelationManager` | **Quick edit addresses** | Batches contact/address fields while keeping country lookups and default toggles. |
| `CategoryResource\RelationManagers\TranslationsRelationManager` | **Quick edit translations** | Provides localized content fields with locale locking after creation to prevent duplicates. |

## Usage tips

- The modal loads existing rows into the repeater. Removing a row deletes the record, editing updates it, and blank `id` entries create new rows.
- Keep validation consistent by updating the relation manager form schema whenever you add/remove fields; the repeater mirrors that schema (plus the hidden `id`).
- To adjust the quick-edit experience (labels, modal size, schema tweaks), edit the corresponding `RelationManagerRepeaterAction::make()` definition inside each relation manager.

## Skipped relation managers

Some relation managers were left unchanged because they rely on attach/detach flows or pivot data that the repeater cannot manage safely:

- `CustomerManagementResource\RelationManagers\AddressesRelationManager` – uses associate/dissociate actions for a BelongsToMany pivot.
- Any `*DocumentsRelationManager`, `*PartnersRelationManager`, etc. that expose complex pivots should continue using their existing attach/edit modals.

Document additional skips in this file if you evaluate more relations in the future.
