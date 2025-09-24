<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SystemResource\Pages;
use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use Filament\Actions\Action as TableAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

    protected static UnitEnum|string|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'system.title';

    protected static ?string $modelLabel = 'system.single';

    protected static ?string $pluralModelLabel = 'system.plural';

    protected static ?string $recordTitleAttribute = 'key';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     */
    public static function getNavigationLabel(): string
    {
        return __('system.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     */
    public static function getNavigationGroup(): ?string
    {
        return 'System';
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('system.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('system.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('System Configuration')
                    ->tabs([
                        Tab::make('Basic Settings')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->components([
                                Section::make('System Information')
                                    ->description(__('system.core_configuration'))
                                    ->components([
                                        Grid::make(2)
                                            ->components([
                                                TextInput::make('key')
                                                    ->label(__('system.setting_key'))
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->unique(ignoreRecord: true)
                                                    ->helperText(__('system.setting_key_help'))
                                                    ->columnSpan(1),
                                                Select::make('category_id')
                                                    ->label(__('system.category'))
                                                    ->relationship('category', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->createOptionForm([
                                                        TextInput::make('name')
                                                            ->label(__('system.category_name'))
                                                            ->required()
                                                            ->maxLength(255),
                                                        Textarea::make('description')
                                                            ->label(__('system.category_description'))
                                                            ->maxLength(500)
                                                            ->rows(3),
                                                        ColorPicker::make('color')
                                                            ->label(__('system.category_color'))
                                                            ->default('#3B82F6'),
                                                        Select::make('parent_id')
                                                            ->label(__('system.parent_category'))
                                                            ->relationship('parent', 'name')
                                                            ->searchable()
                                                            ->preload(),
                                                        TextInput::make('icon')
                                                            ->label(__('system.category_icon'))
                                                            ->placeholder('heroicon-o-cog-6-tooth'),
                                                        TextInput::make('sort_order')
                                                            ->label(__('system.sort_order'))
                                                            ->numeric()
                                                            ->default(0),
                                                        Toggle::make('is_active')
                                                            ->label(__('system.is_active'))
                                                            ->default(true),
                                                    ])
                                                    ->columnSpan(1),
                                            ]),
                                        TextInput::make('name')
                                            ->label(__('system.display_name'))
                                            ->required()
                                            ->maxLength(255)
                                            ->helperText(__('system.display_name_help')),
                                        Textarea::make('description')
                                            ->label(__('system.description'))
                                            ->maxLength(1000)
                                            ->rows(3)
                                            ->helperText(__('system.description_help')),
                                        Textarea::make('help_text')
                                            ->label(__('system.help_text'))
                                            ->maxLength(1000)
                                            ->rows(2)
                                            ->helperText(__('system.help_text_help')),
                                        Grid::make(2)
                                            ->components([
                                                TextInput::make('group')
                                                    ->label(__('system.group'))
                                                    ->maxLength(100)
                                                    ->helperText(__('system.group_help')),
                                                TextInput::make('sort_order')
                                                    ->label(__('system.sort_order'))
                                                    ->numeric()
                                                    ->default(0)
                                                    ->helperText(__('system.sort_order_help')),
                                            ]),
                                        Select::make('type')
                                            ->label(__('system.setting_type'))
                                            ->options([
                                                'string' => __('system.type_string'),
                                                'integer' => __('system.type_integer'),
                                                'boolean' => __('system.type_boolean'),
                                                'json' => __('system.type_json'),
                                                'array' => __('system.type_array'),
                                                'file' => __('system.type_file'),
                                                'color' => __('system.type_color'),
                                                'date' => __('system.type_date'),
                                                'datetime' => __('system.type_datetime'),
                                                'email' => __('system.type_email'),
                                                'url' => __('system.type_url'),
                                                'password' => __('system.type_password'),
                                                'float' => __('system.type_float'),
                                                'text' => __('system.type_text'),
                                                'select' => __('system.type_select'),
                                            ])
                                            ->reactive()
                                            ->helperText(__('system.setting_type_help')),
                                    ]),
                                Section::make('Value Configuration')
                                    ->components([
                                        TextInput::make('value')
                                            ->label(__('system.setting_value'))
                                            ->visible(fn (callable $get) => in_array($get('type'), ['string', 'integer', 'email', 'url', 'password', 'float', 'text']))
                                            ->helperText(__('system.setting_value_help')),
                                        TextInput::make('default_value')
                                            ->label(__('system.default_value'))
                                            ->helperText(__('system.default_value_help')),
                                        TextInput::make('placeholder')
                                            ->label(__('system.placeholder'))
                                            ->maxLength(255)
                                            ->helperText(__('system.placeholder_help')),
                                        TextInput::make('tooltip')
                                            ->label(__('system.tooltip'))
                                            ->maxLength(500)
                                            ->helperText(__('system.tooltip_help')),
                                        Toggle::make('value')
                                            ->label(__('system.enabled'))
                                            ->visible(fn (callable $get) => $get('type') === 'boolean')
                                            ->helperText(__('system.enabled_help')),
                                        ColorPicker::make('value')
                                            ->label(__('system.color_value'))
                                            ->visible(fn (callable $get) => $get('type') === 'color')
                                            ->helperText(__('system.color_value_help')),
                                        DateTimePicker::make('value')
                                            ->label(__('system.date_time'))
                                            ->visible(fn (callable $get) => $get('type') === 'datetime')
                                            ->helperText(__('system.date_time_help')),
                                        DateTimePicker::make('value')
                                            ->label(__('system.date'))
                                            ->displayFormat('Y-m-d')
                                            ->visible(fn (callable $get) => $get('type') === 'date')
                                            ->helperText(__('system.date_help')),
                                        FileUpload::make('value')
                                            ->label(__('system.file_upload'))
                                            ->visible(fn (callable $get) => $get('type') === 'file')
                                            ->helperText(__('system.file_upload_help')),
                                        Select::make('value')
                                            ->label(__('system.select_value'))
                                            ->visible(fn (callable $get) => $get('type') === 'select')
                                            ->options(fn (callable $get) => json_decode($get('options') ?? '{}', true) ?? [])
                                            ->helperText(__('system.select_value_help')),
                                        KeyValue::make('value')
                                            ->label(__('system.key_value_pairs'))
                                            ->visible(fn (callable $get) => $get('type') === 'json')
                                            ->helperText(__('system.key_value_pairs_help')),
                                        Textarea::make('options')
                                            ->label(__('system.options'))
                                            ->visible(fn (callable $get) => in_array($get('type'), ['select', 'json']))
                                            ->helperText(__('system.options_help'))
                                            ->rows(3),
                                        Textarea::make('value')
                                            ->label(__('system.array_items'))
                                            ->visible(fn (callable $get) => $get('type') === 'array')
                                            ->helperText(__('system.array_items_help'))
                                            ->rows(3),
                                    ]),
                            ]),
                        Tab::make('Advanced Settings')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->components([
                                Section::make('Validation & Constraints')
                                    ->components([
                                        Textarea::make('validation_rules')
                                            ->label(__('system.validation_rules'))
                                            ->helperText(__('system.validation_rules_help'))
                                            ->rows(2),
                                        Textarea::make('metadata')
                                            ->label(__('system.metadata'))
                                            ->helperText(__('system.metadata_help'))
                                            ->rows(3),
                                        TextInput::make('tags')
                                            ->label(__('system.tags'))
                                            ->helperText(__('system.tags_help')),
                                        TextInput::make('version')
                                            ->label(__('system.version'))
                                            ->helperText(__('system.version_help')),
                                        TextInput::make('environment')
                                            ->label(__('system.environment'))
                                            ->helperText(__('system.environment_help')),
                                        Grid::make(2)
                                            ->components([
                                                Checkbox::make('is_required')
                                                    ->label(__('system.required_setting'))
                                                    ->helperText(__('system.required_setting_help')),
                                                Checkbox::make('is_encrypted')
                                                    ->label(__('system.encrypt_value'))
                                                    ->helperText(__('system.encrypt_value_help')),
                                                Checkbox::make('is_readonly')
                                                    ->label(__('system.read_only'))
                                                    ->helperText(__('system.read_only_help')),
                                                Checkbox::make('is_public')
                                                    ->label(__('system.public_setting'))
                                                    ->helperText(__('system.public_setting_help')),
                                                Checkbox::make('is_active')
                                                    ->label(__('system.is_active'))
                                                    ->helperText(__('system.is_active_help'))
                                                    ->default(true),
                                                Checkbox::make('is_cacheable')
                                                    ->label(__('system.is_cacheable'))
                                                    ->helperText(__('system.is_cacheable_help')),
                                            ]),
                                    ]),
                                Section::make('Access Control')
                                    ->components([
                                        Select::make('permission_required')
                                            ->label(__('system.required_permission'))
                                            ->options([
                                                'admin' => __('system.permission_admin'),
                                                'manager' => __('system.permission_manager'),
                                                'user' => __('system.permission_user'),
                                                'system' => __('system.permission_system'),
                                            ])
                                            ->helperText(__('system.required_permission_help')),
                                        Select::make('updated_by')
                                            ->label(__('system.updated_by'))
                                            ->relationship('updatedBy', 'name')
                                            ->default(auth()->id())
                                            ->disabled()
                                            ->helperText(__('system.updated_by_help')),
                                    ]),
                                Section::make('System Integration')
                                    ->components([
                                        TextInput::make('cache_key')
                                            ->label(__('system.cache_key'))
                                            ->helperText(__('system.cache_key_help')),
                                        Select::make('cache_ttl')
                                            ->label(__('system.cache_ttl'))
                                            ->options([
                                                0 => __('system.no_cache'),
                                                60 => __('system.1_minute'),
                                                300 => __('system.5_minutes'),
                                                900 => __('system.15_minutes'),
                                                3600 => __('system.1_hour'),
                                                86400 => __('system.1_day'),
                                            ])
                                            ->default(3600)
                                            ->helperText(__('system.cache_ttl_help')),
                                    ]),
                            ]),
                        Tab::make('Translations')
                            ->icon('heroicon-o-language')
                            ->components([
                                Section::make('Multi-language Support')
                                    ->components([
                                        Repeater::make('translations')
                                            ->label(__('system.translations'))
                                            ->relationship('translations')
                                            ->schema([
                                                TextInput::make('locale')
                                                    ->label(__('system.locale'))
                                                    ->required()
                                                    ->maxLength(5),
                                                TextInput::make('name')
                                                    ->label(__('system.translated_name'))
                                                    ->maxLength(255),
                                                Textarea::make('description')
                                                    ->label(__('system.translated_description'))
                                                    ->maxLength(1000)
                                                    ->rows(2),
                                                Textarea::make('help_text')
                                                    ->label(__('system.translated_help_text'))
                                                    ->maxLength(1000)
                                                    ->rows(2),
                                            ])
                                            ->columns(2)
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => $state['locale'] ?? null),
                                    ]),
                            ]),
                        Tab::make('Dependencies & Relations')
                            ->icon('heroicon-o-link')
                            ->components([
                                Section::make('Dependencies')
                                    ->components([
                                        Repeater::make('dependencies')
                                            ->label(__('system.dependencies'))
                                            ->relationship('dependencies')
                                            ->schema([
                                                Select::make('depends_on_setting_id')
                                                    ->label(__('system.depends_on_setting'))
                                                    ->relationship('dependsOn', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required(),
                                                Textarea::make('condition')
                                                    ->label(__('system.condition'))
                                                    ->helperText(__('system.condition_help'))
                                                    ->rows(2),
                                                Toggle::make('is_active')
                                                    ->label(__('system.is_active'))
                                                    ->default(true),
                                            ])
                                            ->columns(2)
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => $state['dependsOn']['name'] ?? null),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label(__('system.setting_key'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('name')
                    ->label(__('system.display_name'))
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label(__('system.category'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'General' => 'gray',
                        'Security' => 'red',
                        'Performance' => 'blue',
                        'UI/UX' => 'green',
                        'API' => 'purple',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('system.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'string' => 'gray',
                        'integer' => 'blue',
                        'boolean' => 'green',
                        'json' => 'purple',
                        'array' => 'yellow',
                        'file' => 'red',
                        default => 'gray',
                    }),
                TextColumn::make('group')
                    ->label(__('system.group'))
                    ->badge()
                    ->color('info'),
                TextColumn::make('value')
                    ->label(__('system.value'))
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
                            return $state ? __('admin.yes') : __('admin.no');
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
                    ->label(__('system.required'))
                    ->boolean(),
                IconColumn::make('is_public')
                    ->label(__('system.public'))
                    ->boolean(),
                IconColumn::make('is_readonly')
                    ->label(__('system.read_only'))
                    ->boolean(),
                IconColumn::make('is_encrypted')
                    ->label(__('system.encrypted'))
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label(__('system.active'))
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label(__('system.sort_order'))
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('system.created_by'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('system.created'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('system.updated'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->preload()
                    ->searchable(),
                SelectFilter::make('type')
                    ->options([
                        'string' => __('system.type_string'),
                        'integer' => __('system.type_integer'),
                        'boolean' => __('system.type_boolean'),
                        'json' => __('system.type_json'),
                        'array' => __('system.type_array'),
                        'file' => __('system.type_file'),
                        'color' => __('system.type_color'),
                        'date' => __('system.type_date'),
                        'datetime' => __('system.type_datetime'),
                        'email' => __('system.type_email'),
                        'url' => __('system.type_url'),
                        'password' => __('system.type_password'),
                        'float' => __('system.type_float'),
                        'text' => __('system.type_text'),
                        'select' => __('system.type_select'),
                    ]),
                SelectFilter::make('group')
                    ->options([
                        'general' => __('system.group_general'),
                        'security' => __('system.group_security'),
                        'performance' => __('system.group_performance'),
                        'ui_ux' => __('system.group_ui_ux'),
                        'api' => __('system.group_api'),
                    ]),
                TernaryFilter::make('is_required')
                    ->label(__('system.required_settings'))
                    ->placeholder(__('system.all_settings'))
                    ->trueLabel(__('system.required_only'))
                    ->falseLabel(__('system.optional_only')),
                TernaryFilter::make('is_public')
                    ->label(__('system.public_settings'))
                    ->placeholder(__('system.all_settings'))
                    ->trueLabel(__('system.public_only'))
                    ->falseLabel(__('system.private_only')),
                TernaryFilter::make('is_readonly')
                    ->label(__('system.read_only_settings'))
                    ->placeholder(__('system.all_settings'))
                    ->trueLabel(__('system.readonly_only'))
                    ->falseLabel(__('system.editable_only')),
                TernaryFilter::make('is_encrypted')
                    ->label(__('system.encrypted_settings'))
                    ->placeholder(__('system.all_settings'))
                    ->trueLabel(__('system.encrypted_only'))
                    ->falseLabel(__('system.unencrypted_only')),
                TernaryFilter::make('is_active')
                    ->label(__('system.active_settings'))
                    ->placeholder(__('system.all_settings'))
                    ->trueLabel(__('system.active_only'))
                    ->falseLabel(__('system.inactive_only')),
                Filter::make('has_dependencies')
                    ->label(__('system.has_dependencies'))
                    ->query(fn (Builder $query): Builder => $query->whereHas('dependencies')),
                Filter::make('has_translations')
                    ->label(__('system.has_translations'))
                    ->query(fn (Builder $query): Builder => $query->whereHas('translations')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                TableAction::make('clear_cache')
                    ->label(__('system.clear_cache'))
                    ->icon('heroicon-o-trash')
                    ->color('warning')
                    ->action(function (SystemSetting $record) {
                        Cache::forget($record->cache_key ?? $record->key);
                        \Filament\Notifications\Notification::make()
                            ->title(__('system.cache_cleared'))
                            ->body(__('system.cache_cleared_for_setting', ['key' => $record->key]))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (SystemSetting $record): bool => ! empty($record->cache_key)),
                TableAction::make('export')
                    ->label(__('system.export'))
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
                        $filename = "setting_{$record->key}_".now()->format('Y-m-d_H-i-s').'.json';

                        return response()
                            ->json($data)
                            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
                    }),
                TableAction::make('view_dependencies')
                    ->label(__('system.view_dependencies'))
                    ->icon('heroicon-o-link')
                    ->color('info')
                    ->action(function (SystemSetting $record) {
                        $dependencies = $record->dependencies()->with('dependsOn')->get();
                        $dependents = $record->dependents()->with('setting')->get();

                        $message = __('system.dependencies_info', [
                            'dependencies_count' => $dependencies->count(),
                            'dependents_count' => $dependents->count(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title(__('system.dependencies'))
                            ->body($message)
                            ->info()
                            ->send();
                    })
                    ->visible(fn (SystemSetting $record): bool => $record->dependencies()->exists() || $record->dependents()->exists()),
                TableAction::make('view_history')
                    ->label(__('system.view_history'))
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->action(function (SystemSetting $record) {
                        $history = $record->history()->with('user')->limit(5)->get();
                        $message = __('system.history_info', ['count' => $history->count()]);

                        \Filament\Notifications\Notification::make()
                            ->title(__('system.history'))
                            ->body($message)
                            ->info()
                            ->send();
                    })
                    ->visible(fn (SystemSetting $record): bool => $record->history()->exists()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    TableAction::make('clear_all_cache')
                        ->label(__('system.clear_all_cache'))
                        ->icon('heroicon-o-trash')
                        ->color('warning')
                        ->action(function (Collection $records) {
                            $cleared = 0;
                            foreach ($records as $record) {
                                if (! empty($record->cache_key)) {
                                    Cache::forget($record->cache_key);
                                    $cleared++;
                                }
                            }
                            \Filament\Notifications\Notification::make()
                                ->title(__('system.cache_cleared'))
                                ->body(__('system.cache_cleared_for_settings', ['count' => $cleared]))
                                ->success()
                                ->send();
                        }),
                    TableAction::make('export_selected')
                        ->label(__('system.export_selected'))
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
                            $filename = 'system_settings_'.now()->format('Y-m-d_H-i-s').'.json';

                            return response()
                                ->json($data->toArray())
                                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
                        }),
                    TableAction::make('activate_selected')
                        ->label(__('system.activate_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $records->each(function (SystemSetting $record) {
                                $record->update(['is_active' => true]);
                            });
                            \Filament\Notifications\Notification::make()
                                ->title(__('system.settings_activated'))
                                ->body(__('system.settings_activated_count', ['count' => $records->count()]))
                                ->success()
                                ->send();
                        }),
                    TableAction::make('deactivate_selected')
                        ->label(__('system.deactivate_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records) {
                            $records->each(function (SystemSetting $record) {
                                $record->update(['is_active' => false]);
                            });
                            \Filament\Notifications\Notification::make()
                                ->title(__('system.settings_deactivated'))
                                ->body(__('system.settings_deactivated_count', ['count' => $records->count()]))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->headerActions([
                TableAction::make('system_health')
                    ->label(__('system.system_health'))
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
                            ->title(__('system.system_health_check'))
                            ->body(json_encode($health, JSON_PRETTY_PRINT))
                            ->info()
                            ->send();
                    }),
                TableAction::make('optimize_system')
                    ->label(__('system.optimize_system'))
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->action(function () {
                        Artisan::call('config:cache');
                        Artisan::call('route:cache');
                        Artisan::call('view:cache');
                        \Filament\Notifications\Notification::make()
                            ->title(__('system.system_optimized'))
                            ->body(__('system.system_optimized_message'))
                            ->success()
                            ->send();
                    }),
                TableAction::make('clear_all_caches')
                    ->label(__('system.clear_all_caches'))
                    ->icon('heroicon-o-trash')
                    ->color('warning')
                    ->action(function () {
                        Artisan::call('cache:clear');
                        Artisan::call('config:clear');
                        Artisan::call('route:clear');
                        Artisan::call('view:clear');
                        \Filament\Notifications\Notification::make()
                            ->title(__('system.all_caches_cleared'))
                            ->body(__('system.all_caches_cleared_message'))
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    /**
     * Get the pages for this resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSystems::route('/'),
            'create' => Pages\CreateSystem::route('/create'),
            'view' => Pages\ViewSystem::route('/{record}'),
            'edit' => Pages\EditSystem::route('/{record}/edit'),
        ];
    }

    /**
     * Get navigation badge.
     */
    public static function getNavigationBadge(): ?string
    {
        return (string) self::getModel()::count();
    }

    /**
     * Get navigation badge color.
     */
    public static function getNavigationBadgeColor(): ?string
    {
        $count = self::getModel()::count();

        return match (true) {
            $count > 100 => 'success',
            $count > 50 => 'warning',
            default => 'danger',
        };
    }
}
