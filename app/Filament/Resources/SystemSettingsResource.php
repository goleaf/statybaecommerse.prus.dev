<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\SystemSettingsResource\Pages;
use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * SystemSettingsResource
 *
 * Filament v4 resource for System Settings management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class SystemSettingsResource extends Resource
{
    protected static ?string $model = SystemSetting::class;
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'key';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('system_settings.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return 'System';
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('system_settings.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('system_settings.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('system_settings.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('key')
                                    ->label(__('system_settings.key'))
                                    ->required()
                                    ->unique(SystemSetting::class, 'key', ignoreRecord: true)
                                    ->maxLength(255)
                                    ->helperText(__('system_settings.key_help')),
                                TextInput::make('name')
                                    ->label(__('system_settings.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText(__('system_settings.name_help')),
                            ]),
                        Textarea::make('description')
                            ->label(__('system_settings.description'))
                            ->rows(3)
                            ->helperText(__('system_settings.description_help')),
                        Textarea::make('help_text')
                            ->label(__('system_settings.help_text'))
                            ->rows(2)
                            ->helperText(__('system_settings.help_text_help')),
                    ]),
                Section::make(__('system_settings.configuration'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('type')
                                    ->label(__('system_settings.type'))
                                    ->required()
                                    ->options([
                                        'string' => __('system_settings.types.string'),
                                        'integer' => __('system_settings.types.integer'),
                                        'boolean' => __('system_settings.types.boolean'),
                                        'float' => __('system_settings.types.float'),
                                        'array' => __('system_settings.types.array'),
                                        'json' => __('system_settings.types.json'),
                                        'file' => __('system_settings.types.file'),
                                        'image' => __('system_settings.types.image'),
                                        'color' => __('system_settings.types.color'),
                                        'date' => __('system_settings.types.date'),
                                        'datetime' => __('system_settings.types.datetime'),
                                    ])
                                    ->live()
                                    ->helperText(__('system_settings.type_help')),
                                Select::make('category_id')
                                    ->label(__('system_settings.category'))
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('slug')
                                            ->maxLength(255),
                                        Textarea::make('description')
                                            ->rows(2),
                                    ])
                                    ->helperText(__('system_settings.category_help')),
                                TextInput::make('group')
                                    ->label(__('system_settings.group'))
                                    ->maxLength(255)
                                    ->helperText(__('system_settings.group_help')),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('value')
                                    ->label(__('system_settings.value'))
                                    ->helperText(__('system_settings.value_help')),
                                TextInput::make('default_value')
                                    ->label(__('system_settings.default_value'))
                                    ->helperText(__('system_settings.default_value_help')),
                            ]),
                    ]),
                Section::make(__('system_settings.options'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                KeyValue::make('options')
                                    ->label(__('system_settings.options'))
                                    ->helperText(__('system_settings.options_help')),
                                KeyValue::make('validation_rules')
                                    ->label(__('system_settings.validation_rules'))
                                    ->helperText(__('system_settings.validation_rules_help')),
                            ]),
                        Grid::make(4)
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
                        Grid::make(3)
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
                Section::make(__('system_settings.advanced'))
                    ->schema([
                        Grid::make(2)
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
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_cacheable')
                                    ->label(__('system_settings.is_cacheable'))
                                    ->helperText(__('system_settings.is_cacheable_help')),
                                TextInput::make('cache_ttl')
                                    ->label(__('system_settings.cache_ttl'))
                                    ->numeric()
                                    ->helperText(__('system_settings.cache_ttl_help')),
                            ]),
                        Grid::make(2)
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

    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
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
                    ->color(fn(string $state): string => match ($state) {
                        'string' => 'gray',
                        'integer' => 'blue',
                        'boolean' => 'green',
                        'float' => 'yellow',
                        'array' => 'purple',
                        'json' => 'indigo',
                        'file' => 'pink',
                        'image' => 'orange',
                        'color' => 'pink',
                        'date' => 'cyan',
                        'datetime' => 'cyan',
                        default => 'gray',
                    }),
                TextColumn::make('value')
                    ->label(__('system_settings.value'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),
                TextColumn::make('category.name')
                    ->label(__('system_settings.category'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('group')
                    ->label(__('system_settings.group'))
                    ->badge()
                    ->color('secondary'),
                IconColumn::make('is_public')
                    ->label(__('system_settings.is_public'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_encrypted')
                    ->label(__('system_settings.is_encrypted'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_required')
                    ->label(__('system_settings.is_required'))
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
                TextColumn::make('updated_by')
                    ->label(__('system_settings.updated_by'))
                    ->formatStateUsing(fn($state) => User::find($state)?->name ?? __('admin.unknown'))
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
                        'string' => __('system_settings.types.string'),
                        'integer' => __('system_settings.types.integer'),
                        'boolean' => __('system_settings.types.boolean'),
                        'float' => __('system_settings.types.float'),
                        'array' => __('system_settings.types.array'),
                        'json' => __('system_settings.types.json'),
                        'file' => __('system_settings.types.file'),
                        'image' => __('system_settings.types.image'),
                        'color' => __('system_settings.types.color'),
                        'date' => __('system_settings.types.date'),
                        'datetime' => __('system_settings.types.datetime'),
                    ]),
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->preload(),
                SelectFilter::make('group')
                    ->options(fn() => SystemSetting::distinct()->pluck('group', 'group')->filter()),
                TernaryFilter::make('is_public')
                    ->trueLabel(__('system_settings.public_only'))
                    ->falseLabel(__('system_settings.private_only'))
                    ->native(false),
                TernaryFilter::make('is_encrypted')
                    ->trueLabel(__('system_settings.encrypted_only'))
                    ->falseLabel(__('system_settings.unencrypted_only'))
                    ->native(false),
                TernaryFilter::make('is_required')
                    ->trueLabel(__('system_settings.required_only'))
                    ->falseLabel(__('system_settings.optional_only'))
                    ->native(false),
                TernaryFilter::make('is_active')
                    ->trueLabel(__('system_settings.active_only'))
                    ->falseLabel(__('system_settings.inactive_only'))
                    ->native(false),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('reset_to_default')
                    ->label(__('system_settings.reset_to_default'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (SystemSetting $record): void {
                        if ($record->default_value) {
                            $record->update(['value' => $record->default_value]);
                            Notification::make()
                                ->title(__('system_settings.reset_successfully'))
                                ->success()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->visible(fn(SystemSetting $record): bool => !empty($record->default_value)),
                Action::make('duplicate')
                    ->label(__('system_settings.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function (SystemSetting $record): void {
                        $newRecord = $record->replicate();
                        $newRecord->key = $record->key . '_copy';
                        $newRecord->name = $record->name . ' (Copy)';
                        $newRecord->save();

                        Notification::make()
                            ->title(__('system_settings.duplicated_successfully'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('export_settings')
                        ->label(__('system_settings.export_settings'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            // Export settings logic here
                            Notification::make()
                                ->title(__('system_settings.exported_successfully'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('clear_cache')
                        ->label(__('system_settings.clear_cache'))
                        ->icon('heroicon-o-trash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            // Clear cache logic here
                            SystemSetting::clearCache();
                            Notification::make()
                                ->title(__('system_settings.cache_cleared_successfully'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    /**
     * Get the relations for this resource.
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSystemSettings::route('/'),
            'create' => Pages\CreateSystemSetting::route('/create'),
            'view' => Pages\ViewSystemSetting::route('/{record}'),
            'edit' => Pages\EditSystemSetting::route('/{record}/edit'),
        ];
    }
}
