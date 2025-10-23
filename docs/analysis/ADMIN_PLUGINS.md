# Admin Plugins Documentation

This document provides an overview of all Filament v4 plugins installed and configured in the admin panel.

## Installed Plugins

### 1. Shield - Role & Permission Management
- **Package**: `bezhansalleh/filament-shield`
- **Version**: v4.0.2
- **Purpose**: Complete role-based access control system
- **Features**:
  - Role management
  - Permission assignment
  - Resource-level permissions
  - User role assignment
- **Documentation**: https://filamentphp.com/plugins/bezhansalleh-shield

### 2. Spatie Media Library Plugin
- **Package**: `filament/spatie-laravel-media-library-plugin`
- **Version**: v4.0
- **Purpose**: Native file uploads and image management
- **Features**:
  - File upload components
  - Image columns in tables
  - Media collections
  - Image conversions
- **Documentation**: https://github.com/filamentphp/spatie-laravel-media-library-plugin

### 3. Tiptap Editor
- **Package**: `awcodes/filament-tiptap-editor`
- **Version**: v4.0
- **Purpose**: Modern rich text editor
- **Features**:
  - WYSIWYG editing
  - Multiple profiles
  - Custom formatting
  - Image insertion
- **Documentation**: https://filamentphp.com/plugins/tiptap

### 4. Excel Export
- **Package**: `pxlrbt/filament-excel`
- **Version**: v3.1.0
- **Purpose**: Export tables to Excel/CSV
- **Features**:
  - Bulk export actions
  - Custom column mapping
  - Multiple formats (XLSX, CSV)
  - Queued exports
- **Documentation**: https://filamentphp.com/plugins/pxlrbt-excel

### 5. Filament Logger
- **Package**: `jacobtims/filament-logger`
- **Purpose**: Activity logging integration
- **Features**:
  - Automatic model change tracking
  - Activity log resource
  - User action logging
  - Customizable log options
- **Documentation**: https://github.com/Jacobtims/filament-logger

### 6. Log Manager
- **Package**: `filipfonal/filament-log-manager`
- **Purpose**: View and manage application logs
- **Features**:
  - Log file browser
  - Log download
  - Log clearing
  - Real-time log viewing
- **Documentation**: https://filamentphp.com/plugins/log-manager

### 7. Overlook
- **Package**: `awcodes/overlook`
- **Purpose**: Application overview dashboard
- **Features**:
  - Resource count widgets
  - System overview
  - Quick navigation
  - Customizable display
- **Documentation**: https://github.com/awcodes/overlook

### 8. Resource Lock
- **Package**: `kenepa/resource-lock`
- **Purpose**: Prevent concurrent editing
- **Features**:
  - Record locking during editing
  - Lock status indicators
  - Automatic lock release
  - Conflict resolution
- **Documentation**: https://filamentphp.com/plugins/kenepa-resource-lock

### 9. Socialite
- **Package**: `dutchcodingcompany/filament-socialite`
- **Version**: v3.0.0
- **Purpose**: OAuth authentication
- **Features**:
  - Google OAuth
  - GitHub OAuth
  - Facebook OAuth
  - Custom OAuth providers
- **Documentation**: https://filamentphp.com/plugins/socialite

### 10. Bolt - Form Builder
- **Package**: `lara-zeus/bolt`
- **Purpose**: Dynamic form builder
- **Features**:
  - Visual form builder
  - Frontend form rendering
  - Form submissions management
  - Custom field types
- **Documentation**: https://filamentphp.com/plugins/lara-zeus-bolt

### 11. Inline Chart - Table Sparklines
- **Package**: `lara-zeus/inline-chart`
- **Version**: v2.0.4
- **Purpose**: Render reusable sparkline charts inside Filament tables
- **Features**:
  - Lightweight inline charts for list views
  - Reusable Livewire widgets for products and customers
  - Cached helpers for product (`App\\Support\\Stats\\Series\\ProductSeries::dailySales`) and customer (`App\\Support\\Stats\\Series\\CustomerSeries::dailyOrders`) activity
  - Seamless integration via `LaraZeus\\InlineChart\\Tables\\Columns\\InlineChart`
- **Usage**: Apply the `InlineChart` column in table definitions and point it to `App\\Filament\\Widgets\\InlineCharts\\ProductSalesSparkline` or `CustomerOrdersSparkline` to share the cached datasets.

### 12. Spatie Translatable Locale Switcher
- **Package**: `lara-zeus/spatie-translatable`
- **Purpose**: Adds a locale switcher to Filament resources backed by `spatie/laravel-translatable`
- **Configuration**:
  - `defaultLocales([...])`: Populated from `config('shared.localization.supported_locales')`; update `config/shared.php` to change the languages available in the admin and storefront.
  - `persist()`: Enabled in `App\Providers\Filament\AdminPanelProvider` so editors resume in their last used locale after navigating away or signing back in.
- **Resource integration**:
  - Resources include the aliased `LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable` trait to expose locale-aware form fields.
  - Resource pages (`Create*`, `Edit*`, `List*`, `View*`) mix in the matching traits from the package so the toolbar locale switcher and the form payload stay aligned with the active language.
- **Further reading**: See the [Admin Translations Guide](analysis/FILAMENT_V4_IMPLEMENTATION_SUMMARY.md#spatie-translatable) for end-to-end usage patterns and translation tab conventions.

## Demo Implementation

The project includes a complete demonstration of all plugins through the `Post` model and `PostResource`:

### Post Model Features
- **Activity Logging**: All changes are tracked
- **Media Library**: Featured image and gallery support
- **Rich Content**: Tiptap editor for content creation
- **SEO Fields**: Meta title and description
- **Status Management**: Draft, published, archived states

### PostResource Features
- **Rich Text Editor**: Tiptap editor for content
- **File Uploads**: Spatie Media Library integration
- **Excel Export**: Bulk export with custom columns
- **Activity Tracking**: Automatic change logging
- **Resource Locking**: Prevents concurrent editing
- **Advanced Filters**: Status, author, date ranges
- **Media Columns**: Thumbnail display in table

## Configuration

All plugins are configured in `app/Providers/Filament/AdminPanelProvider.php`:

```php
->plugins([
    FilamentShieldPlugin::make(),
    FilamentLoggerPlugin::make(),
    OverlookPlugin::make(),
    ResourceLockPlugin::make(),
    FilamentSocialitePlugin::make(),
    FilamentLogManagerPlugin::make(),
])
```

## Usage Examples

### Media Library Integration
```php
// In form
SpatieMediaLibraryFileUpload::make('images')
    ->collection('images')
    ->multiple(false)
    ->image()
    ->imageEditor();

// In table
SpatieMediaLibraryImageColumn::make('images')
    ->collection('images')
    ->conversion('thumb');
```

### Excel Export
```php
ExportBulkAction::make()
    ->exports([
        ExcelExport::make()
            ->fromTable()
            ->withFilename(fn () => 'posts-' . date('Y-m-d'))
            ->withColumns([
                Column::make('title')->heading('Title'),
                Column::make('user.name')->heading('Author'),
                // ... more columns
            ]),
    ])
```

### Activity Logging
```php
// In model
use Spatie\Activitylog\Traits\LogsActivity;

public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly(['title', 'content', 'status'])
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
}
```

## Troubleshooting

### Common Issues
1. **Version Conflicts**: Use `--with-all-dependencies` when installing
2. **Navigation Type Errors**: Remove typed properties, use docblocks
3. **Media Library**: Ensure proper trait usage and collection definitions
4. **Excel Export**: Handle large datasets with chunking
5. **Resource Locking**: Test concurrent editing scenarios

### Support
- Check individual plugin documentation for specific issues
- Review the `.cursor/filament-plugins-docs.mdc` file for detailed guidance
- Ensure all plugins are compatible with Filament v4

## Future Enhancements

Consider adding these additional plugins:
- **Curator**: Advanced media management
- **Breezy**: User management (when v4 compatible)
- **Notifications**: Enhanced notification system
- **Charts**: Data visualization widgets
- **Calendar**: Event and scheduling management

### Export column contract

Reusable bulk exports rely on the `App\Services\Export\Contracts\DefinesExportColumns` interface. Resources implement `availableExportColumns()` and return keyed `ExportColumn` instances that resolve table values on demand.

```php
use App\Services\Export\Contracts\DefinesExportColumns;
use App\Services\Export\ExportColumn;

/** @return array<string, ExportColumn> */
public static function availableExportColumns(): array
{
    return [
        'name' => new ExportColumn('name', __('products.fields.name'), fn (Product $product): string => (string) $product->name),
        // ...
    ];
}
```

Bulk actions feed the selected column keys and `ExportFormat` into `ExportService::queueResourceExport()`, which dispatches the queued job, persists the artifact, and notifies the requester when the signed download URL is ready.
