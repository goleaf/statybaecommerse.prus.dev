---
inclusion: fileMatch
fileMatchPattern: 'app/Filament/**/*.php'
---

# Filament v4 rules (scoped)

## Imports
- Ensure these imports exist when needed:
  - `use Filament\Schemas\Schema;`
  - `use Filament\Tables\Table;`
- Remove any conflicting import:
  - `use App\Filament\Resources\Schema;`

## Resource signatures (v4)
- Always use:
  - `public static function form(Schema $schema): Schema`
  - `public static function table(Table $table): Table`
- Inside `form()`, the parameter variable should be `$schema` (not `$form`).

## Ban conflicting local class names
- No class may exist with FQN `App\Filament\Resources\Schema`. If found, rename/move it and update references.

## Testing
- Prefer Pest feature tests: page mounts, authz enforced, key actions succeed.
- Use Boost `search-docs` for exact Filament v4 APIs when unsure.
