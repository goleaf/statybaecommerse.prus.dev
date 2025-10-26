# Table Layout Toggle

Our Filament admin tables now support switching between list and grid layouts using the [Hydrat Agency table layout toggle plugin](https://github.com/Hydrat-Agency/filament-table-layout-toggle).

## Toggle placement
- The toggle button renders automatically in each table toolbar immediately after the search input (`tables::toolbar.search.after`).
- Both buttons use Heroicons (`heroicon-o-list-bullet` for list, `heroicon-o-squares-2x2` for grid).

## Default layout and persistence
- First-time visitors land in the **grid** layout.
- The selection persists per-page in the browser via the plugin's `LocalStoragePersister`. No server round-trips are required.
- The plugin is configured with Redis connection details so switching to the cache persister in the future is a single line change.

## How it works
- All `ListRecords` pages extend `App\Filament\Pages\Support\BaseListRecords`, which applies the toggleable layout automatically.
- Relation managers extend `App\Filament\RelationManagers\Support\BaseRelationManager` for the same behaviour.
- Custom pages or components that implement `HasTable` (for example, inventory dashboards) pull in the `ConfiguresToggleableTableLayout` trait and call `applyToggleableTableLayout()` inside their `table()` definitions.
- Grid cards render through the `App\Filament\Tables\Columns\GridLayoutColumn` view component, which converts visible columns into a responsive card layout while keeping column actions, formatting, and toggles intact.

## Adding the toggle to new tables
1. For new list pages, extend `BaseListRecords`. For relation managers, extend `BaseRelationManager`. For custom `HasTable` components, `use` both `HasToggleableTable` and `ConfiguresToggleableTableLayout`, then return `applyToggleableTableLayout($table)` in `table()`.
2. Define columns as usual inside the resource or relation manager. The helper automatically reuses visible columns for the grid cards.
3. If you need a bespoke grid card, override `applyToggleableTableLayout()` in your component or supply a custom `GridLayoutColumn` implementation.

That is all—every table receives the toggle without duplicating schema code.
