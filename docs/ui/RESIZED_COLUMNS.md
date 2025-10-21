# Table Column Resizing

Filament v4 ships with built-in column resizing on every table definition. Our resources lean on the stock `Table` builder, so any table configured under `app/Filament/Resources/**/Tables` automatically opts into the resizing UX. For example, `ShippingOptionsTable::configure()` only wires columns, filters, and actions—the core builder handles sizing affordances for us. 【F:app/Filament/Resources/ShippingOptions/Tables/ShippingOptionsTable.php†L18-L88】

## Persistence model
- **Primary store – user preferences JSON.** Width choices are saved into the `users.preferences` JSON column that was introduced for Filament table state. The migration ensures the column exists, and the `User` model casts it to an array so nested keys (such as `tables.product-list.columnWidths`) can be written safely. 【F:database/migrations/2025_09_03_000005_enhance_filament_tables.php†L60-L74】【F:app/Models/User.php†L90-L107】
- **Fallback – browser storage.** When a write to the preferences column is not possible (guest sessions, read-only replicas, or transient network issues), Filament keeps the widths in `localStorage`. We already rely on the `LocalStoragePersister` for other table UI state via the table layout toggle plugin, so column widths inherit the same client-side safety net. 【F:app/Providers/Filament/AdminPanelProvider.php†L111-L131】

## Resetting widths
1. Clear the persisted keys inside `users.preferences` (for example, via `php artisan tinker` and `Arr::forget($user->preferences, 'tables.*')`). The model cast will accept the updated array on save. 【F:app/Models/User.php†L90-L107】
2. Ask the affected admin to refresh—Filament will fall back to the default widths and rehydrate fresh values.

## Troubleshooting
If resized columns appear stuck or styles look stale, rebuild the panel assets and hard refresh the browser:

```bash
php artisan filament:assets
```

After the command completes, force-reload the page (Shift + Reload / Cmd + Shift + R) so the browser pulls the new JavaScript bundle.
