# Relation Manager Repeater quick-edit actions

The `zvizvi/relation-manager-repeater` plugin (v2.x) is now rolled out across the Filament admin panel. Eligible HasMany relation managers expose a **Quick edit** header action that opens a repeater-powered modal and syncs the submitted rows back to the underlying relationship, keeping in-line adjustments fast without abandoning the table view.

## Enabled relation managers

| Resource | Relation manager | Relationship | Action label | Repeater highlights |
| --- | --- | --- | --- | --- |
| `ProductResource` | `ImagesRelationManager` | `HasMany images` | **Quick edit images** | Reuses the image upload flow plus metadata fields, supports cloning/reordering, and keeps existing records stable through a hidden `id` input. |
| `ProductResource` | `VariantsRelationManager` | `HasMany variants` | **Quick edit variants** | Focuses on pricing, inventory, and publish toggles; variant attributes stay editable in the standard form. |
| `OrderResource` | `OrderItemsRelationManager` | `HasMany orderItems` | **Quick edit items** | Lets operators adjust quantity, pricing, and notes without reopening each row; product and SKU stay read-only safeguards. |
| `UserResource` | `AddressesRelationManager` | `HasMany addresses` | **Quick edit addresses** | Batches contact/address fields while keeping country lookups and default toggles. |
| `CategoryResource` | `TranslationsRelationManager` | `HasMany translations` | **Quick edit translations** | Provides localized content fields with locale locking after creation to prevent duplicates. |

## Usage tips

- The modal loads existing rows into the repeater. Removing a row deletes the record, editing updates it, and blank `id` entries create new rows.
- Keep validation consistent by updating the relation manager form schema whenever you add/remove fields; the repeater mirrors that schema (plus the hidden `id`).
- To adjust the quick-edit experience (labels, modal size, schema tweaks), edit the corresponding `RelationManagerRepeaterAction::make()` definition inside each relation manager.

## Eligibility guidance

Use the repeater action for relations that:

- Manage high-churn HasMany data where multiple rows frequently change together.
- Share the same schema as the existing create/edit form so validation remains aligned.
- Benefit from reordering, cloning, or quick toggles without exposing destructive pivot edits.

Opt out of the action when a relation manager:

- Relies on attach/detach flows (typical for BelongsToMany or pivot-heavy relationships).
- Requires nested repeaters or deeply relational fields that do not serialize cleanly in the modal.
- Performs side effects during save that assume single-record edits.

When new relation managers are introduced, evaluate them against the criteria above and document the outcome here so the global rollout stays consistent.
