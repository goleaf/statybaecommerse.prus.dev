# Relation Manager Repeater quick-edit actions

The `zvizvi/relation-manager-repeater` plugin (v2.x) is installed to provide bulk editing modals for high-volume HasMany relations. Each enabled relation manager now exposes a **Quick edit** header action that opens a repeater-powered modal and syncs the submitted rows back to the underlying relationship. As of the latest admin refresh, any relation manager that renders a create action now pairs it with a repeater configured via `BaseRelationManager::getQuickEditSchema()`, ensuring we reuse the exact form schema plus a hidden `id` to keep updates idempotent.

## Enabled relation managers

Every HasMany relation manager that previously surfaced `CreateAction::make()` now ships with a quick-edit partner. Highlights worth calling out:

- **Catalogues** – categories, brands, attributes, collections, price lists, and stock relations now let merchandisers batch-edit metadata without visiting each record individually.
- **Customer data** – customer, user, and user-management relations (addresses, orders, referrals, loyalty) expose repeaters so support teams can triage cases faster.
- **Marketing & content** – news, campaigns, discounts, coupons, and legal translations benefit from mirrored schemas for rapid proofreading or toggling visibility.
- **Operations** – order shipping/documents, variant stock, and currency price relations gained inline adjustments to streamline fulfilment and finance reviews.

Refer to the corresponding relation manager class (for example `Channels\RelationManagers\ProductsRelationManager`) to see the `RelationManagerRepeaterAction::make()` implementation and any context-specific modal wording.

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

## Related docs

- [Searchable input metadata lifecycle](SEARCHABLE_INPUT_METADATA.md) – guidance for hydrating searchable field metadata alongside relation manager repeaters.
