# Filament Schema Signature Upgrade

## Summary
Filament v4 now expects resources, pages, and relation managers to use the schema-based builders introduced in the compatibility releases. Legacy `Form`/`Table`/`Infolist` return signatures (`Form|array`, `Table|array`, `Infolist|array`) triggered fatal errors after the latest package discovery run.

## Required Signatures
All Filament entry points must implement the following method signatures:

```php
public static function form(Schema $form): Schema;
public static function table(Table $table): Table;
public static function infolist(Schema $schema): Schema;
```

Relation managers implement the non-static equivalents:

```php
public function form(Schema $form): Schema;
public function table(Table $table): Table;
public function infolist(Schema $schema): Schema; // when applicable
```

Make sure the file imports `use Filament\Schemas\Schema;` alongside the existing `use Filament\Tables\Table;` declaration.

## Migration Notes
- The variable name can remain `$form`/`$table`/`$schema`; only the type and return declarations changed.
- Existing form components continue to work; no component refactors are required beyond the signature update.
- When adding new resources or pages, copy these method signatures to avoid regression during `composer install` or `php artisan package:discover`.

Document updated: 2025-10-21
