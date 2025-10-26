# Table Column Resizing

Filament's tables ship with first-class column resizing, and the admin panel enables it through the `asmit/resized-column` plugin. Every List page under `app/Filament` pulls in a guard-aware wrapper so administrators always see the widths they previously configured. 【F:app/Filament/Pages/Support/BaseListRecords.php†L8-L22】【F:app/Filament/Concerns/HasResizableColumns.php†L9-L73】

## Persistence model

- **Database store – `table_settings`.** The plugin is registered via the admin panel provider with database persistence turned on. Column widths are saved per admin user and per resource row inside the dedicated table that ships with the project. 【F:app/Providers/Filament/AdminPanelProvider.php†L108-L164】【F:database/migrations/2025_05_16_183635_create_table_settings_table.php†L11-L24】
- **Session fallback – guard scoped keys.** Even when the database is temporarily unavailable, the plugin writes widths into the session. The wrapper trait appends the panel identifier and user ID to the session key, preventing collisions with storefront guards or other Filament panels. 【F:app/Filament/Concerns/HasResizableColumns.php†L41-L63】

## Guard-aware persistence

`App\Filament\Concerns\HasResizableColumns` proxies the vendor trait but resolves the authenticated admin via `Filament::getPanel('admin')->auth()->id()`. The guard lookup runs through an overridable `getResizableColumnPanelId()` helper, so downstream panels can opt in without rewriting persistence. Any class that mixes in the trait transparently reads/writes widths for the signed-in admin guard. 【F:app/Filament/Concerns/HasResizableColumns.php†L17-L73】

### Opting in from other panels or guards

1. Create/extend a Filament class and `use App\Filament\Concerns\HasResizableColumns`.
2. Override `protected static function getResizableColumnPanelId(): string` to return the panel ID that should drive authentication (for example, `'support'` or `'manager'`).
3. Ensure the target panel is registered with the corresponding guard and that the guard authenticates users capable of persisting column widths.

Because the wrapper trait already scopes the session key and database writes to the resolved guard, no additional persistence overrides are necessary.

## Resetting widths

1. Remove the stored widths for a user/resource pair from the `table_settings` table—either via `php artisan tinker` and a `DB::table('table_settings')->where('user_id', ...)->where('resource', ...)->delete();` call or by truncating the table for a full reset. 【F:database/migrations/2025_05_16_183635_create_table_settings_table.php†L11-L24】
2. Ask the administrator to refresh the page. The plugin will recreate the session cache and database row the next time the table loads.

## Shared list record scaffolding
- Role listings now extend `App\Filament\Pages\Support\BaseListRecords` so they inherit the shared table sizing hooks alongside the default header actions. Future resources with similar column sizing needs should follow the same inheritance pattern to avoid diverging configuration. 【F:app/Filament/Resources/RoleResource/Pages/ListRoles.php†L7-L24】

## Troubleshooting

If resized columns appear stuck or the JavaScript fails to load, rebuild the Filament assets and force-refresh the browser:

```bash
php artisan filament:assets
```

After the command completes, perform a hard reload (Shift + Reload / Cmd + Shift + R) so the browser picks up the new bundle. 【F:app/Providers/Filament/AdminPanelProvider.php†L28-L164】
