<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SystemSettingResource\Pages;
use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use App\Services\SystemSettingsService;
use BackedEnum;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use UnitEnum;

final class SystemSettingResource extends Resource
{
    /**
     * Explicitly wire the backing model so Filament resolves the resource correctly.
     */
    protected static ?string $model = SystemSetting::class;

    /**
     * @var string|BackedEnum|null ensure Filament v4 compatible navigation icon metadata.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    /**
     * @var string|UnitEnum|null keep the resource grouped under the shared Settings sidebar bucket.
     */
    protected static UnitEnum|string|null $navigationGroup = 'Settings';

    /**
     * Store the column used for record titles so Filament table headers match the legacy UI.
     */
    protected static ?string $recordTitleAttribute = 'key';

    public static function getRecordTitleAttribute(): string
    {
        // Guarantee downstream helpers always receive the canonical key-based label even
        // if upstream mixins reset the cached property during schema registration.
        return self::$recordTitleAttribute ?? 'key';
    }

    public static function getNavigationLabel(): string
    {
        return __('system_settings.title');
    }

    // Navigation group handled by property

    public static function getPluralModelLabel(): string
    {
        return __('system_settings.plural');
    }

    public static function getModelLabel(): string
    {
        return __('system_settings.single');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaSection::make(__('system_settings.basic_information'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('key')
                                    ->label(__('system_settings.key'))
                                    ->required()
                                    ->unique(SystemSetting::class, 'key', ignoreRecord: true)
                                    ->maxLength(255)
                                    // Guardrail so reviewers understand why we surface helper copy.
                                    ->helperText(__('system_settings.key_help')),
                                TextInput::make('name')
                                    ->label(__('system_settings.name'))
                                    ->required()
                                    ->maxLength(255)
                                    // Name stays mandatory to keep translation sync tooling reliable.
                                    ->helperText(__('system_settings.name_help')),
                            ]),
                        Select::make('category_id')
                            ->label(__('system_settings.category'))
                            // Target the explicit relation helper to avoid column name collisions with the legacy `category` attribute.
                            ->relationship('categoryRelation', 'name')
                            ->searchable()
                            ->preload()
                            ->required() // Keep the category link mandatory so reporting scopes retain context.
                            ->createOptionForm([
                                // Allowing inline category creation keeps admin flows self-contained.
                                TextInput::make('name')
                                    ->label(__('system_setting_categories.name'))
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('slug')
                                    ->label(__('system_setting_categories.slug'))
                                    ->required()
                                    ->maxLength(255),
                                Textarea::make('description')
                                    ->label(__('system_setting_categories.description'))
                                    ->rows(2),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                // Ensure new categories are immediately usable by marking them active.
                                $category = SystemSettingCategory::create([
                                    'name'        => $data['name'],
                                    'slug'        => $data['slug'],
                                    'description' => $data['description'] ?? null,
                                    'is_active'   => true,
                                ]);

                                return (int) $category->getKey();
                            })
                            ->helperText(__('system_settings.category_help')),
                        Textarea::make('description')
                            ->label(__('system_settings.description'))
                            ->rows(3)
                            ->helperText(__('system_settings.description_help')),
                        Textarea::make('help_text')
                            ->label(__('system_settings.help_text'))
                            ->rows(2)
                            ->helperText(__('system_settings.help_text_help')),
                    ]),
                SchemaSection::make(__('system_settings.configuration'))
                    ->schema([
                        SchemaGrid::make(3)
                            ->schema([
                                Select::make('type')
                                    ->label(__('system_settings.type'))
                                    ->required()
                                    ->options([
                                        'string'   => __('system_settings.types.string'),
                                        'email'    => 'Email',
                                        'url'      => 'URL',
                                        'password' => 'Password',
                                        'integer'  => __('system_settings.types.integer'),
                                        'boolean'  => __('system_settings.types.boolean'),
                                        'float'    => __('system_settings.types.float'),
                                        'array'    => __('system_settings.types.array'),
                                        'json'     => __('system_settings.types.json'),
                                        'file'     => __('system_settings.types.file'),
                                        'image'    => __('system_settings.types.image'),
                                        'color'    => __('system_settings.types.color'),
                                        'date'     => __('system_settings.types.date'),
                                        'datetime' => __('system_settings.types.datetime'),
                                    ])
                                    ->live()
                                    ->helperText(__('system_settings.type_help')),
                                TextInput::make('group')
                                    ->label(__('system_settings.group'))
                                    ->maxLength(255)
                                    ->helperText(__('system_settings.group_help')),
                                TextInput::make('unit')
                                    ->label(__('system_settings.unit'))
                                    ->maxLength(255)
                                    ->helperText(__('system_settings.unit_help')),
                            ]),
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('value')
                                    ->label(__('system_settings.value'))
                                    ->helperText(__('system_settings.value_help')),
                                TextInput::make('default_value')
                                    ->label(__('system_settings.default_value'))
                                    ->helperText(__('system_settings.default_value_help')),
                            ]),
                    ]),
                SchemaSection::make(__('system_settings.options'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                KeyValue::make('options')
                                    ->label(__('system_settings.options'))
                                    // Key/value interface keeps select driven settings human readable.
                                    ->helperText(__('system_settings.options_help')),
                                KeyValue::make('validation_rules')
                                    ->label(__('system_settings.validation_rules'))
                                    ->helperText(__('system_settings.validation_rules_help')),
                            ]),
                        SchemaGrid::make(4)
                            ->schema([
                                Toggle::make('is_public')
                                    ->label(__('system_settings.is_public'))
                                    ->helperText(__('system_settings.is_public_help')),
                                Toggle::make('is_required')
                                    ->label(__('system_settings.is_required'))
                                    ->helperText(__('system_settings.is_required_help')),
                                Toggle::make('is_encrypted')
                                    ->label(__('system_settings.is_encrypted'))
                                    ->helperText(__('system_settings.is_encrypted_help')),
                                Toggle::make('is_readonly')
                                    ->label(__('system_settings.is_readonly'))
                                    ->helperText(__('system_settings.is_readonly_help')),
                            ]),
                        SchemaGrid::make(3)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label(__('system_settings.is_active'))
                                    ->default(true)
                                    ->helperText(__('system_settings.is_active_help')),
                                TextInput::make('sort_order')
                                    ->label(__('system_settings.sort_order'))
                                    ->numeric()
                                    ->default(0)
                                    ->helperText(__('system_settings.sort_order_help')),
                                TextInput::make('placeholder')
                                    ->label(__('system_settings.placeholder'))
                                    ->maxLength(255)
                                    ->helperText(__('system_settings.placeholder_help')),
                            ]),
                    ]),
                SchemaSection::make(__('system_settings.advanced'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('tooltip')
                                    ->label(__('system_settings.tooltip'))
                                    ->maxLength(255)
                                    ->helperText(__('system_settings.tooltip_help')),
                                TextInput::make('validation_message')
                                    ->label(__('system_settings.validation_message'))
                                    ->maxLength(255)
                                    ->helperText(__('system_settings.validation_message_help')),
                            ]),
                        SchemaGrid::make(2)
                            ->schema([
                                Toggle::make('is_cacheable')
                                    ->label(__('system_settings.is_cacheable'))
                                    ->helperText(__('system_settings.is_cacheable_help')),
                                TextInput::make('cache_ttl')
                                    ->label(__('system_settings.cache_ttl'))
                                    ->numeric()
                                    ->helperText(__('system_settings.cache_ttl_help')),
                            ]),
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('environment')
                                    ->label(__('system_settings.environment'))
                                    ->maxLength(255)
                                    ->helperText(__('system_settings.environment_help')),
                                TextInput::make('tags')
                                    ->label(__('system_settings.tags'))
                                    ->maxLength(255)
                                    ->helperText(__('system_settings.tags_help')),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label(__('system_settings.key'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('name')
                    ->label(__('system_settings.name'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('type')
                    ->label(__('system_settings.type'))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'string'   => 'gray',
                        'integer'  => 'blue',
                        'boolean'  => 'green',
                        'float'    => 'yellow',
                        'array'    => 'purple',
                        'json'     => 'indigo',
                        'file'     => 'pink',
                        'image'    => 'orange',
                        'color'    => 'pink',
                        'date'     => 'cyan',
                        'datetime' => 'cyan',
                        default    => 'gray',
                    }),
                TextColumn::make('value')
                    ->label(__('system_settings.value'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        $state = is_scalar($state) ? (string) $state : '';

                        return strlen($state) > 50 ? $state : null;
                    })
                    ->formatStateUsing(function ($state, SystemSetting $record): string {
                        $stringState = is_scalar($state) ? (string) $state : '';
                        if ($record->type === 'password') {
                            return str_repeat('*', min(strlen($stringState), 8));
                        }
                        if ($record->type === 'boolean') {
                            return ($stringState === '1' || strtolower($stringState) === 'true')
                                ? __('system_settings.yes')
                                : __('system_settings.no');
                        }

                        return $stringState;
                    }),
                TextColumn::make('categoryRelation.name')
                    ->label(__('system_settings.category'))
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    // Surfacing the related category keeps multi-lingual labels intact.
                    ->formatStateUsing(fn (?string $state): string => $state ?? '-'),
                TextColumn::make('group')
                    ->label(__('system_settings.group'))
                    ->badge()
                    ->color('secondary')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('default_value')
                    ->label(__('system_settings.default_value'))
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('unit')
                    ->label(__('system_settings.unit'))
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_public')
                    ->label(__('system_settings.is_public'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_required')
                    ->label(__('system_settings.is_required'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_encrypted')
                    ->label(__('system_settings.is_encrypted'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_readonly')
                    ->label(__('system_settings.is_readonly'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('system_settings.is_active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('system_settings.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('system_settings.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('system_settings.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'string'   => __('system_settings.types.string'),
                        'integer'  => __('system_settings.types.integer'),
                        'boolean'  => __('system_settings.types.boolean'),
                        'float'    => __('system_settings.types.float'),
                        'array'    => __('system_settings.types.array'),
                        'json'     => __('system_settings.types.json'),
                        'file'     => __('system_settings.types.file'),
                        'image'    => __('system_settings.types.image'),
                        'color'    => __('system_settings.types.color'),
                        'date'     => __('system_settings.types.date'),
                        'datetime' => __('system_settings.types.datetime'),
                    ]),
                SelectFilter::make('category_id')
                    ->label(__('system_settings.category'))
                    // Target the explicit relation helper so Filament can safely hydrate the filter options.
                    ->relationship('categoryRelation', 'name'),
                SelectFilter::make('group')
                    ->label(__('system_settings.group'))
                    ->options(fn (): array => SystemSetting::query()
                        ->whereNotNull('group')
                        ->distinct()
                        ->orderBy('group')
                        ->pluck('group', 'group')
                        ->toArray()),
                TernaryFilter::make('is_active')
                    ->trueLabel(__('system_settings.active_only'))
                    ->falseLabel(__('system_settings.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_public')
                    ->trueLabel(__('system_settings.public_only'))
                    ->falseLabel(__('system_settings.private_only'))
                    ->native(false),
                TernaryFilter::make('is_required')
                    ->trueLabel(__('system_settings.required_only'))
                    ->falseLabel(__('system_settings.optional_only'))
                    ->native(false),
                TernaryFilter::make('is_encrypted')
                    ->trueLabel(__('system_settings.encrypted_only'))
                    ->falseLabel(__('system_settings.unencrypted_only'))
                    ->native(false),
                TernaryFilter::make('is_readonly')
                    ->trueLabel(__('system_settings.readonly_only'))
                    ->falseLabel(__('system_settings.editable_only'))
                    ->native(false),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                TableDeleteAction::make()
                    // Rely on the default soft-delete so audit history remains intact.
                    ->requiresConfirmation(),
                Action::make('reset_to_default')
                    ->label(__('system_settings.reset_to_default'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (SystemSetting $record): bool => ! empty($record->default_value))
                    ->action(function (SystemSetting $record): void {
                        $record->update(['value' => $record->default_value]);
                        Notification::make()
                            ->title(__('system_settings.reset_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('duplicate')
                    ->label(__('system_settings.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (SystemSetting $record): void {
                        // Generate a readable copy suffix while keeping the key unique.
                        $baseKey = $record->key . '_copy';
                        $newKey = $baseKey;
                        $counter = 2;
                        while (SystemSetting::where('key', $newKey)->exists()) {
                            $newKey = $baseKey . '_' . $counter;
                            $counter++;
                        }

                        $duplicate = $record->replicate();
                        $duplicate->key = $newKey;
                        $duplicate->name = sprintf('%s (Copy)', (string) $record->name);
                        $duplicate->save();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        // Default deletion keeps soft-deleted records recoverable.
                        ->requiresConfirmation(),
                    BulkAction::make('activate')
                        ->label(__('system_settings.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('system_settings.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('system_settings.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('system_settings.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('reset_to_default')
                        ->label(__('system_settings.reset_selected_to_default'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each(function (SystemSetting $record): void {
                                if (! empty($record->default_value)) {
                                    $record->update(['value' => $record->default_value]);
                                }
                            });
                            Notification::make()
                                ->title(__('system_settings.bulk_reset_to_default_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('export_settings')
                        ->label(__('system_settings.export_settings'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Collection $records): void {
                            // Trigger the export service so downstream jobs receive the cached payload.
                            app(SystemSettingsService::class)->exportSettings();
                            Notification::make()
                                ->title(__('notifications.export_started'))
                                ->info()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('clear_cache')
                        ->label(__('system_settings.clear_cache'))
                        ->icon('heroicon-o-trash')
                        ->color('warning')
                        ->action(function (): void {
                            // Clearing the cache after bulk operations prevents stale admin reads.
                            app(SystemSettingsService::class)->clearCache();
                            Notification::make()
                                ->title(__('system_settings.cache_cleared_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSystemSettings::route('/'),
            'create' => Pages\CreateSystemSetting::route('/create'),
            'view'   => Pages\ViewSystemSetting::route('/{record}'),
            'edit'   => Pages\EditSystemSetting::route('/{record}/edit'),
        ];
    }

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'system-settings';
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return SystemSetting::query()->withoutGlobalScopes();
    }

    // duplicate method removed
}
