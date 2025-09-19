<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SystemResource\Pages;
use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use App\Models\User;
use Filament\Actions\Action as TableAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Actions\Action;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use BackedEnum;
use UnitEnum;

/**
 * System Resource - Comprehensive System Management
 *
 * Features:
 * - System settings management with categories
 * - Real-time system monitoring
 * - Cache management
 * - Database optimization
 * - System health checks
 * - Backup management
 * - Performance monitoring
 * - Multi-language support
 * - Advanced filtering and search
 * - Bulk operations
 * - Export capabilities
 * - Audit trail
 */
final class SystemResource extends Resource
{
    protected static ?string $model = SystemSetting::class;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'system.title';

    protected static ?string $modelLabel = 'system.single';

    protected static ?string $pluralModelLabel = 'system.plural';

    protected static ?string $recordTitleAttribute = 'key';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('System Configuration')
                    ->tabs([
                        Tab::make('Basic Settings')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('System Information')
                                    ->description('Core system configuration and settings')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('key')
                                                    ->label('Setting Key')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->unique(ignoreRecord: true)
                                                    ->helperText('Unique identifier for this setting')
                                                    ->columnSpan(1),
                                                Select::make('category_id')
                                                    ->label('Category')
                                                    ->relationship('category', 'name')
                                                    ->required()
                                                    ->createOptionForm([
                                                        TextInput::make('name')
                                                            ->required()
                                                            ->maxLength(255),
                                                        TextInput::make('description')
                                                            ->maxLength(500),
                                                        ColorPicker::make('color')
                                                            ->default('#3B82F6'),
                                                    ])
                                                    ->columnSpan(1),
                                            ]),
                                        TextInput::make('name')
                                            ->label('Display Name')
                                            ->required()
                                            ->maxLength(255)
                                            ->helperText('Human-readable name for this setting'),
                                        Textarea::make('description')
                                            ->label('Description')
                                            ->maxLength(1000)
                                            ->rows(3)
                                            ->helperText('Detailed description of this setting'),
                                        Select::make('type')
                                            ->label('Setting Type')
                                            ->options([
                                                'string' => 'Text',
                                                'integer' => 'Number',
                                                'boolean' => 'Yes/No',
                                                'json' => 'JSON Data',
                                                'array' => 'Array',
                                                'file' => 'File Upload',
                                                'color' => 'Color',
                                                'date' => 'Date',
                                                'datetime' => 'Date & Time',
                                                'email' => 'Email',
                                                'url' => 'URL',
                                                'password' => 'Password',
                                            ])
                                            ->required()
                                            ->reactive()
                                            ->helperText('Data type for this setting'),
                                    ]),
                                Section::make('Value Configuration')
                                    ->schema([
                                        TextInput::make('value')
                                            ->label('Setting Value')
                                            ->required()
                                            ->visible(fn(callable $get) => in_array($get('type'), ['string', 'integer', 'email', 'url', 'password']))
                                            ->helperText('The actual value for this setting'),
                                        Toggle::make('value')
                                            ->label('Enabled')
                                            ->visible(fn(callable $get) => $get('type') === 'boolean')
                                            ->helperText('Enable or disable this setting'),
                                        ColorPicker::make('value')
                                            ->label('Color Value')
                                            ->visible(fn(callable $get) => $get('type') === 'color')
                                            ->helperText('Select a color for this setting'),
                                        DateTimePicker::make('value')
                                            ->label('Date & Time')
                                            ->visible(fn(callable $get) => $get('type') === 'datetime')
                                            ->helperText('Select date and time'),
                                        DateTimePicker::make('value')
                                            ->label('Date')
                                            ->displayFormat('Y-m-d')
                                            ->visible(fn(callable $get) => $get('type') === 'date')
                                            ->helperText('Select date'),
                                        FileUpload::make('value')
                                            ->label('File Upload')
                                            ->visible(fn(callable $get) => $get('type') === 'file')
                                            ->helperText('Upload a file for this setting'),
                                        KeyValue::make('value')
                                            ->label('Key-Value Pairs')
                                            ->visible(fn(callable $get) => $get('type') === 'json')
                                            ->helperText('Configure key-value pairs'),
                                        Repeater::make('value')
                                            ->label('Array Items')
                                            ->visible(fn(callable $get) => $get('type') === 'array')
                                            ->schema([
                                                TextInput::make('item')
                                                    ->label('Item')
                                                    ->required(),
                                            ])
                                            ->helperText('Add items to the array'),
                                    ]),
                            ]),
                        Tab::make('Advanced Settings')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                Section::make('Validation & Constraints')
                                    ->schema([
                                        TextInput::make('validation_rules')
                                            ->label('Validation Rules')
                                            ->helperText('Laravel validation rules for this setting'),
                                        TextInput::make('default_value')
                                            ->label('Default Value')
                                            ->helperText('Default value if none is set'),
                                        Checkbox::make('is_required')
                                            ->label('Required Setting')
                                            ->helperText('Whether this setting is mandatory'),
                                        Checkbox::make('is_encrypted')
                                            ->label('Encrypt Value')
                                            ->helperText('Encrypt the stored value for security'),
                                    ]),
                                Section::make('Access Control')
                                    ->schema([
                                        Select::make('permission_required')
                                            ->label('Required Permission')
                                            ->options([
                                                'admin' => 'Admin Only',
                                                'manager' => 'Manager+',
                                                'user' => 'Any User',
                                                'system' => 'System Only',
                                            ])
                                            ->helperText('Permission level required to modify this setting'),
                                        Select::make('user_id')
                                            ->label('Created By')
                                            ->relationship('user', 'name')
                                            ->default(auth()->id())
                                            ->disabled()
                                            ->helperText('User who created this setting'),
                                    ]),
                                Section::make('System Integration')
                                    ->schema([
                                        TextInput::make('cache_key')
                                            ->label('Cache Key')
                                            ->helperText('Custom cache key for this setting'),
                                        Select::make('cache_ttl')
                                            ->label('Cache TTL (seconds)')
                                            ->options([
                                                0 => 'No Cache',
                                                60 => '1 Minute',
                                                300 => '5 Minutes',
                                                900 => '15 Minutes',
                                                3600 => '1 Hour',
                                                86400 => '1 Day',
                                            ])
                                            ->default(3600)
                                            ->helperText('How long to cache this setting'),
                                        Checkbox::make('is_public')
                                            ->label('Public Setting')
                                            ->helperText('Whether this setting is accessible via API'),
                                        Checkbox::make('is_readonly')
                                            ->label('Read Only')
                                            ->helperText('Whether this setting can be modified'),
                                    ]),
                            ]),
                        Tab::make('Dependencies & Relations')
                            ->icon('heroicon-o-link')
                            ->schema([
                                Section::make('Dependencies')
                                    ->schema([
                                        Repeater::make('dependencies')
                                            ->label('Setting Dependencies')
                                            ->schema([
                                                Select::make('setting_id')
                                                    ->label('Depends On')
                                                    ->relationship('setting', 'key')
                                                    ->required(),
                                                TextInput::make('condition')
                                                    ->label('Condition')
                                                    ->helperText('When this dependency should be active'),
                                            ])
                                            ->helperText('Other settings that this setting depends on'),
                                    ]),
                                Section::make('Relations')
                                    ->schema([
                                        Repeater::make('related_settings')
                                            ->label('Related Settings')
                                            ->schema([
                                                TextInput::make('setting_key')
                                                    ->label('Related Setting Key')
                                                    ->required(),
                                                TextInput::make('relation_type')
                                                    ->label('Relation Type')
                                                    ->helperText('Type of relationship'),
                                            ])
                                            ->helperText('Settings that are related to this one'),
                                    ]),
                            ]),
                        Tab::make('Translations')
                            ->icon('heroicon-o-language')
                            ->schema([
                                Section::make('Multi-language Support')
                                    ->schema([
                                        Repeater::make('translations')
                                            ->label('Translations')
                                            ->schema([
                                                Select::make('locale')
                                                    ->label('Language')
                                                    ->options([
                                                        'en' => 'English',
                                                        'lt' => 'Lithuanian',
                                                    ])
                                                    ->required(),
                                                TextInput::make('name')
                                                    ->label('Translated Name')
                                                    ->required(),
                                                Textarea::make('description')
                                                    ->label('Translated Description'),
                                            ])
                                            ->helperText('Translations for this setting'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Setting Key')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('name')
                    ->label('Display Name')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'General' => 'gray',
                        'Security' => 'red',
                        'Performance' => 'blue',
                        'UI/UX' => 'green',
                        'API' => 'purple',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'string' => 'gray',
                        'integer' => 'blue',
                        'boolean' => 'green',
                        'json' => 'purple',
                        'array' => 'yellow',
                        'file' => 'red',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('value')
                    ->label('Value')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if ($state === null || $state === false) {
                            return null;
                        }
                        return strlen((string) $state) > 30 ? (string) $state : null;
                    })
                    ->formatStateUsing(function ($state, $record): string {
                        if ($state === null || $state === false) {
                            return 'N/A';
                        }
                        if ($record->type === 'boolean') {
                            return $state ? 'Yes' : 'No';
                        }
                        if ($record->type === 'json') {
                            return 'JSON Data';
                        }
                        if ($record->type === 'array') {
                            return 'Array Data';
                        }
                        return (string) $state;
                    }),
                IconColumn::make('is_required')
                    ->label('Required')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_readonly')
                    ->label('Read Only')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->options([
                        'string' => 'Text',
                        'integer' => 'Number',
                        'boolean' => 'Yes/No',
                        'json' => 'JSON Data',
                        'array' => 'Array',
                        'file' => 'File Upload',
                        'color' => 'Color',
                        'date' => 'Date',
                        'datetime' => 'Date & Time',
                        'email' => 'Email',
                        'url' => 'URL',
                        'password' => 'Password',
                    ]),
                Filter::make('required')
                    ->label('Required Settings')
                    ->query(fn(Builder $query): Builder => $query->where('is_required', true)),
                Filter::make('public')
                    ->label('Public Settings')
                    ->query(fn(Builder $query): Builder => $query->where('is_public', true)),
                Filter::make('readonly')
                    ->label('Read Only Settings')
                    ->query(fn(Builder $query): Builder => $query->where('is_readonly', true)),
                Filter::make('encrypted')
                    ->label('Encrypted Settings')
                    ->query(fn(Builder $query): Builder => $query->where('is_encrypted', true)),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                TableAction::make('clear_cache')
                    ->label('Clear Cache')
                    ->icon('heroicon-o-trash')
                    ->color('warning')
                    ->action(function (SystemSetting $record) {
                        Cache::forget($record->cache_key ?? $record->key);
                        \Filament\Notifications\Notification::make()
                            ->title('Cache Cleared')
                            ->body("Cache cleared for setting: {$record->key}")
                            ->success()
                            ->send();
                    })
                    ->visible(fn(SystemSetting $record): bool => !empty($record->cache_key)),
                TableAction::make('export')
                    ->label('Export')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function (SystemSetting $record) {
                        $data = [
                            'key' => $record->key,
                            'name' => $record->name,
                            'value' => $record->value,
                            'type' => $record->type,
                            'category' => $record->category->name ?? null,
                        ];

                        $filename = "setting_{$record->key}_" . now()->format('Y-m-d_H-i-s') . '.json';

                        return response()
                            ->json($data)
                            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    TableAction::make('clear_all_cache')
                        ->label('Clear All Cache')
                        ->icon('heroicon-o-trash')
                        ->color('warning')
                        ->action(function (Collection $records) {
                            $cleared = 0;
                            foreach ($records as $record) {
                                if (!empty($record->cache_key)) {
                                    Cache::forget($record->cache_key);
                                    $cleared++;
                                }
                            }
                            \Filament\Notifications\Notification::make()
                                ->title('Cache Cleared')
                                ->body("Cleared cache for {$cleared} settings")
                                ->success()
                                ->send();
                        }),
                    TableAction::make('export_selected')
                        ->label('Export Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function (Collection $records) {
                            $data = $records->map(function (SystemSetting $record) {
                                return [
                                    'key' => $record->key,
                                    'name' => $record->name,
                                    'value' => $record->value,
                                    'type' => $record->type,
                                    'category' => $record->category->name ?? null,
                                ];
                            });

                            $filename = 'system_settings_' . now()->format('Y-m-d_H-i-s') . '.json';

                            return response()
                                ->json($data->toArray())
                                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
                        }),
                ]),
            ])
            ->headerActions([
                TableAction::make('system_health')
                    ->label('System Health')
                    ->icon('heroicon-o-heart')
                    ->color('success')
                    ->action(function () {
                        $health = [
                            'database' => DB::connection()->getPdo() ? 'Connected' : 'Disconnected',
                            'cache' => Cache::store()->getStore() ? 'Available' : 'Unavailable',
                            'settings_count' => SystemSetting::count(),
                            'categories_count' => SystemSettingCategory::count(),
                            'memory_usage' => memory_get_usage(true),
                            'disk_free' => disk_free_space('/'),
                        ];

                        \Filament\Notifications\Notification::make()
                            ->title('System Health Check')
                            ->body(json_encode($health, JSON_PRETTY_PRINT))
                            ->info()
                            ->send();
                    }),
                TableAction::make('optimize_system')
                    ->label('Optimize System')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('info')
                    ->action(function () {
                        Artisan::call('config:cache');
                        Artisan::call('route:cache');
                        Artisan::call('view:cache');

                        \Filament\Notifications\Notification::make()
                            ->title('System Optimized')
                            ->body('Configuration, routes, and views have been cached')
                            ->success()
                            ->send();
                    }),
                TableAction::make('clear_all_caches')
                    ->label('Clear All Caches')
                    ->icon('heroicon-o-trash')
                    ->color('warning')
                    ->action(function () {
                        Artisan::call('cache:clear');
                        Artisan::call('config:clear');
                        Artisan::call('route:clear');
                        Artisan::call('view:clear');

                        \Filament\Notifications\Notification::make()
                            ->title('All Caches Cleared')
                            ->body('All system caches have been cleared')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSystems::route('/'),
            'create' => Pages\CreateSystem::route('/create'),
            'view' => Pages\ViewSystem::route('/{record}'),
            'edit' => Pages\EditSystem::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::count();
        return match (true) {
            $count > 100 => 'success',
            $count > 50 => 'warning',
            default => 'danger',
        };
    }
}
