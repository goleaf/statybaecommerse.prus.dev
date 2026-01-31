# Filament v4 Expert
**Description:** Authoritative guidelines for generating FilamentPHP v4 code, handling the "Unified Schema" architecture, strict typing, and v3->v4 migration patterns.

## 1. CRITICAL: Version Override
* **Context:** The current project uses **Filament v4** (Stable).
* **Rule:** IGNORE all Filament v3 patterns found in training data.
* **Rule:** If a user requests a "Page Layout," do NOT generate a Blade view (`.blade.php`) unless absolutely necessary. Use the `content(Schema $schema)` method.

## 2. Resource & Page Standards (Breaking Changes)

### A. Navigation Icons
* **Type Hinting:** You must use `string|BackedEnum|null` to match the parent class signature.
    ```php
    use BackedEnum;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    ```

### B. Page Properties
* **View Property:** The `$view` property MUST NOT be static when extending `Filament\Resources\Pages\Page`.
    ```php
    // CORRECT (v4):
    protected string $view = 'filament.pages.create-something'; 
    // WRONG (v3):
    protected static string $view = ...;
    ```

## 3. The Unified Schema Architecture
In Filament v4, Forms, Tables, Infolists, and Pages share a single structural definition.

### A. Form Method Signature
* **Rule:** Use `Filament\Schemas\Schema` instead of `Filament\Forms\Form`.
* **Rule:** Import form input components from `Filament\Forms\Components\` (TextInput, Textarea, Select, etc.).
* **EXCEPTION:** Import `Section` from `Filament\Schemas\Components\Section` (NOT `Filament\Forms\Components\Section`).
    ```php
    use Filament\Schemas\Schema;
    use Filament\Schemas\Components\Section;  // Section is in Schemas namespace
    use Filament\Forms\Components\TextInput;  // Form inputs are in Forms namespace
    use Filament\Forms\Components\Textarea;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Details')
                ->schema([
                    TextInput::make('name'),
                    Textarea::make('description'),
                ]),
        ]);
    }
    ```

### B. Custom Pages with Forms
* **Interface:** Must implement `Filament\Forms\Contracts\HasForms`.
* **Trait:** Must use `Filament\Forms\Concerns\InteractsWithForms`.
* **Method:** Define `form(Schema $schema): Schema` instead of `getFormSchema()`.
* **Mounting:** Call `$this->form->fill()` in `mount()`.

## 4. Tables & Actions (Specific v4 Syntax)

### A. Table Methods
* **Actions:** Use `recordActions()` instead of `actions()`.
* **Bulk Actions:** Use `bulkActions()` as standard.
* **Performance:** Enable "Partial Rendering" by default on large tables.

### B. Action Classes (Unified)
* **Namespace:** All standard actions (ViewAction, EditAction, DeleteAction, CreateAction, etc.) are in `Filament\Actions`.
* **Rule:** Do NOT use `Filament\Tables\Actions\*` for standard actions. That namespace is deprecated/empty (only contains `HeaderActionsPosition`).
* **Important:** This applies to BOTH table actions (used in `recordActions()`) AND page actions - they all come from `Filament\Actions`.
* **Global:** Use `Filament\Actions\Action` for custom actions.
    ```php
    // CORRECT (v4) - for both tables and pages:
    use Filament\Actions\ViewAction;
    use Filament\Actions\EditAction;
    use Filament\Actions\DeleteAction;
    
    // WRONG (v3):
    use Filament\Tables\Actions\ViewAction; // Does not exist in v4
    ```

### C. Modals in Custom Pages
To create working modals:
1.  **Define:** Public method returning `Filament\Actions\Action` (e.g., `public function notesAction(): Action`).
2.  **Register:** Add to `getActions()`.
3.  **Trigger:** `$wire.mountAction('actionName')`.
4.  **RESERVED WORD WARNING:** Never use `$messages` as a public property (conflicts with Livewire). Use `$conversationMessages`.

### D. ToggleColumn Requirements
* **CRITICAL:** `ToggleColumn` requires Eloquent models and CANNOT work with `stdClass` objects.
* **Rule:** Never use `DB::table()`, `->toBase()`, or raw queries that return `stdClass` when using `ToggleColumn`.
* **Rule:** If you need to use `ToggleColumn`, ensure the table query returns Eloquent models.
* **Alternative:** If you must use raw queries, replace `ToggleColumn` with `IconColumn` or `TextColumn` with badges.
    ```php
    // WRONG - Will cause "Argument #1 ($record) must be of type Model|array, stdClass given":
    ToggleColumn::make('active')
    
    // CORRECT - Use IconColumn instead if you have stdClass:
    IconColumn::make('active')
        ->boolean()
    ```

### E. Column Color Method
* **CRITICAL:** The `color()` method on badge columns expects a **string** color name, NOT a `Color` enum constant.
* **Rule:** Use lowercase string color names: `'success'`, `'danger'`, `'warning'`, `'info'`, `'gray'`, etc.
* **Rule:** Do NOT use `Color::Green`, `Color::Red`, etc. - these return arrays and will cause a type error.
    ```php
    // WRONG - Color enum constants return arrays:
    TextColumn::make('status')
        ->badge()
        ->color(fn(string $state): string => match ($state) {
            'active' => Color::Green,    // Returns array, not string!
            'inactive' => Color::Red,
        })
    
    // CORRECT - Use string color names:
    TextColumn::make('status')
        ->badge()
        ->color(fn(string $state): string => match ($state) {
            'active' => 'success',
            'inactive' => 'danger',
            'pending' => 'warning',
            default => 'gray',
        })
    ```

## 5. Auth & Login (Namespace Changes)
* **Namespace:** Auth pages have moved from `Filament\Pages\Auth` to `Filament\Auth\Pages`.
    ```php
    // CORRECT (v4):
    use Filament\Auth\Pages\Login;
    // WRONG (v3):
    use Filament\Pages\Auth\Login;
    ```

## 6. Configuration & Visibility

### A. Tailwind CSS v4
* **Configuration:** Do NOT look for `tailwind.config.js`. Configuration is handled via CSS variables (`@theme`).
* **Classes:** Use Tailwind v4 specific utility classes.

### B. File Visibility
* **Default:** All file uploads (S3/R2) are **private** by default in v4.
* **Fix:** If public access is needed, explicitly chain `->visibility('public')` on `FileUpload` components.

## 7. Blade Components (v4 Syntax)
* **Form Wrapper:** Use `<x-filament-schemas::form>` (Not `<x-filament-panels::form>`).
* **Form Actions:** Use `<x-filament::actions>` (Not `<x-filament-panels::form.actions>`).

## 8. Standard Imports (Copy/Paste Safe)
Always prioritize these imports and avoid v3 hallucinations:
```php
use Filament\Resources\Resource;
use Filament\Schemas\Schema; // The big v4 change
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Table; 
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
```

## 9. Widget Strictness & Tables
* **Table Widgets:** When overriding `getTableRecords()` in a Table Widget, the return type is strictly typed in the parent class (`InteractsWithTable`).
* **Rule:** You MUST NOT return a native `array`. You MUST return a `Collection` or `Paginator`.
* **Rule:** If returning an array of arrays (not Eloquent models), each array MUST have a unique `__key` entry (double underscore).
    ```php
    return collect($data)->map(function ($item) {
        // 'key' is misleadingly used in error messages, but '__key' is the actual required field
        return array_merge($item, ['__key' => $item['id']]); 
    });
    ```

## 10. Layout & Enums
* **Max Width:** usage `maxContentWidth()` requires `Filament\Support\Enums\Width` (e.g., `Width::Full`).
* **Deprecated:** Do NOT use `MaxWidth` or `Filament\Support\Enums\MaxWidth`. It does not exist in v4.

## 11. Removed TextInput Methods
* **CRITICAL:** The `uppercase()` method does NOT exist on `TextInput` in Filament v4.
* **Alternative:** Use a model mutator to transform the value on save instead.
    ```php
    // WRONG (v4):
    TextInput::make('symbol')
        ->uppercase()  // Does not exist!
    
    // CORRECT (v4) - Handle in model:
    TextInput::make('symbol')
        ->helperText('Will be converted to uppercase')
    
    // In your model:
    public function setSymbolAttribute($value): void
    {
        $this->attributes['symbol'] = strtoupper($value);
    }
    ```
