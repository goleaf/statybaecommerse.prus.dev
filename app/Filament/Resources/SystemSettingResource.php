<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\SystemSettingResource\Pages;
use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final /**
 * SystemSettingResource
 * 
 * Filament resource for admin panel management.
 */
class SystemSettingResource extends Resource
{
    protected static ?string $model = SystemSetting::class;

    /** @var string|\BackedEnum|null */
    protected static $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'System Settings';

    protected static ?string $modelLabel = 'System Setting';

    protected static ?string $pluralModelLabel = 'System Settings';

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::System->label();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make(__('admin.system_settings.setting_information'))
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label(__('admin.system_settings.category'))
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('admin.system_settings.category_name'))
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('slug')
                                    ->label(__('admin.system_settings.category_slug'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(SystemSettingCategory::class, 'slug'),
                                Forms\Components\Textarea::make('description')
                                    ->label(__('admin.system_settings.category_description'))
                                    ->maxLength(1000),
                                Forms\Components\TextInput::make('icon')
                                    ->label(__('admin.system_settings.category_icon'))
                                    ->maxLength(255)
                                    ->placeholder('heroicon-o-cog-6-tooth'),
                                Forms\Components\Select::make('color')
                                    ->label(__('admin.system_settings.category_color'))
                                    ->options([
                                        'primary' => 'Primary',
                                        'secondary' => 'Secondary',
                                        'success' => 'Success',
                                        'warning' => 'Warning',
                                        'danger' => 'Danger',
                                        'info' => 'Info',
                                    ])
                                    ->default('primary'),
                                Forms\Components\TextInput::make('sort_order')
                                    ->label(__('admin.system_settings.sort_order'))
                                    ->numeric()
                                    ->default(0),
                            ]),
                        Forms\Components\TextInput::make('key')
                            ->label(__('admin.system_settings.key'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText(__('admin.system_settings.key_help')),
                        Forms\Components\TextInput::make('name')
                            ->label(__('admin.system_settings.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('group')
                            ->label(__('admin.system_settings.group'))
                            ->options([
                                'general' => 'General',
                                'ecommerce' => 'E-commerce',
                                'email' => 'Email',
                                'payment' => 'Payment',
                                'shipping' => 'Shipping',
                                'seo' => 'SEO',
                                'security' => 'Security',
                                'api' => 'API',
                                'appearance' => 'Appearance',
                                'notifications' => 'Notifications',
                            ])
                            ->required()
                            ->default('general'),
                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('admin.system_settings.sort_order'))
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('placeholder')
                            ->label(__('admin.system_settings.placeholder'))
                            ->maxLength(255)
                            ->helperText(__('admin.system_settings.placeholder_help')),
                        Forms\Components\Textarea::make('tooltip')
                            ->label(__('admin.system_settings.tooltip'))
                            ->maxLength(500)
                            ->rows(2)
                            ->helperText(__('admin.system_settings.tooltip_help')),
                        Forms\Components\KeyValue::make('metadata')
                            ->label(__('admin.system_settings.metadata'))
                            ->keyLabel(__('admin.system_settings.metadata_key'))
                            ->valueLabel(__('admin.system_settings.metadata_value'))
                            ->helperText(__('admin.system_settings.metadata_help')),
                        Forms\Components\TextInput::make('validation_message')
                            ->label(__('admin.system_settings.validation_message'))
                            ->maxLength(255)
                            ->helperText(__('admin.system_settings.validation_message_help')),
                        Forms\Components\Toggle::make('is_cacheable')
                            ->label(__('admin.system_settings.is_cacheable'))
                            ->default(true)
                            ->helperText(__('admin.system_settings.is_cacheable_help')),
                        Forms\Components\TextInput::make('cache_ttl')
                            ->label(__('admin.system_settings.cache_ttl'))
                            ->numeric()
                            ->default(3600)
                            ->suffix(__('admin.system_settings.seconds'))
                            ->helperText(__('admin.system_settings.cache_ttl_help'))
                            ->visible(fn (Forms\Get $get) => $get('is_cacheable')),
                        Forms\Components\Select::make('environment')
                            ->label(__('admin.system_settings.environment'))
                            ->options([
                                'all' => __('admin.system_settings.all_environments'),
                                'production' => __('admin.system_settings.production'),
                                'staging' => __('admin.system_settings.staging'),
                                'development' => __('admin.system_settings.development'),
                            ])
                            ->default('all')
                            ->required(),
                        Forms\Components\TagsInput::make('tags')
                            ->label(__('admin.system_settings.tags'))
                            ->helperText(__('admin.system_settings.tags_help')),
                        Forms\Components\TextInput::make('version')
                            ->label(__('admin.system_settings.version'))
                            ->default('1.0.0')
                            ->maxLength(20)
                            ->helperText(__('admin.system_settings.version_help')),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('admin.system_settings.value_configuration'))
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label(__('admin.system_settings.type'))
                            ->options([
                                'string' => 'String',
                                'text' => 'Text',
                                'number' => 'Number',
                                'boolean' => 'Boolean',
                                'array' => 'Array',
                                'json' => 'JSON',
                                'file' => 'File',
                                'image' => 'Image',
                                'select' => 'Select',
                                'color' => 'Color',
                                'date' => 'Date',
                                'datetime' => 'DateTime',
                            ])
                            ->required()
                            ->default('string')
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                $set('value', null);
                            }),
                        Forms\Components\TextInput::make('value')
                            ->label(__('admin.system_settings.value'))
                            ->visible(fn (Forms\Get $get) => in_array($get('type'), ['string', 'number', 'color', 'date', 'datetime']))
                            ->required(fn (Forms\Get $get) => $get('is_required')),
                        Forms\Components\Textarea::make('value')
                            ->label(__('admin.system_settings.value'))
                            ->visible(fn (Forms\Get $get) => in_array($get('type'), ['text', 'json']))
                            ->rows(4)
                            ->required(fn (Forms\Get $get) => $get('is_required')),
                        Forms\Components\Toggle::make('value')
                            ->label(__('admin.system_settings.value'))
                            ->visible(fn (Forms\Get $get) => $get('type') === 'boolean')
                            ->default(false),
                        Forms\Components\Select::make('value')
                            ->label(__('admin.system_settings.value'))
                            ->visible(fn (Forms\Get $get) => $get('type') === 'select')
                            ->options(fn (Forms\Get $get) => $get('options') ?? [])
                            ->searchable()
                            ->required(fn (Forms\Get $get) => $get('is_required')),
                        Forms\Components\FileUpload::make('value')
                            ->label(__('admin.system_settings.value'))
                            ->visible(fn (Forms\Get $get) => in_array($get('type'), ['file', 'image']))
                            ->disk('public')
                            ->directory('system-settings')
                            ->acceptedFileTypes(fn (Forms\Get $get) => $get('type') === 'image'
                                ? ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
                                : ['application/pdf', 'text/plain', 'application/json'])
                            ->required(fn (Forms\Get $get) => $get('is_required')),
                        Forms\Components\KeyValue::make('value')
                            ->label(__('admin.system_settings.value'))
                            ->visible(fn (Forms\Get $get) => $get('type') === 'array')
                            ->keyLabel(__('admin.system_settings.key'))
                            ->valueLabel(__('admin.system_settings.value'))
                            ->required(fn (Forms\Get $get) => $get('is_required')),
                        Forms\Components\KeyValue::make('options')
                            ->label(__('admin.system_settings.options'))
                            ->visible(fn (Forms\Get $get) => $get('type') === 'select')
                            ->keyLabel(__('admin.system_settings.option_key'))
                            ->valueLabel(__('admin.system_settings.option_value'))
                            ->helperText(__('admin.system_settings.options_help')),
                        Forms\Components\TextInput::make('default_value')
                            ->label(__('admin.system_settings.default_value'))
                            ->helperText(__('admin.system_settings.default_value_help')),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('admin.system_settings.advanced_options'))
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label(__('admin.system_settings.description'))
                            ->maxLength(1000)
                            ->rows(3),
                        Forms\Components\Textarea::make('help_text')
                            ->label(__('admin.system_settings.help_text'))
                            ->maxLength(1000)
                            ->rows(3)
                            ->helperText(__('admin.system_settings.help_text_help')),
                        Forms\Components\KeyValue::make('validation_rules')
                            ->label(__('admin.system_settings.validation_rules'))
                            ->keyLabel(__('admin.system_settings.rule_name'))
                            ->valueLabel(__('admin.system_settings.rule_value'))
                            ->helperText(__('admin.system_settings.validation_rules_help')),
                        Forms\Components\Toggle::make('is_public')
                            ->label(__('admin.system_settings.is_public'))
                            ->helperText(__('admin.system_settings.is_public_help')),
                        Forms\Components\Toggle::make('is_required')
                            ->label(__('admin.system_settings.is_required'))
                            ->helperText(__('admin.system_settings.is_required_help')),
                        Forms\Components\Toggle::make('is_encrypted')
                            ->label(__('admin.system_settings.is_encrypted'))
                            ->helperText(__('admin.system_settings.is_encrypted_help')),
                        Forms\Components\Toggle::make('is_readonly')
                            ->label(__('admin.system_settings.is_readonly'))
                            ->helperText(__('admin.system_settings.is_readonly_help')),
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('admin.system_settings.is_active'))
                            ->default(true)
                            ->helperText(__('admin.system_settings.is_active_help')),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('admin.system_settings.category'))
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(fn ($record) => $record->category?->color ?? 'primary'),
                Tables\Columns\TextColumn::make('key')
                    ->label(__('admin.system_settings.key'))
                    ->sortable()
                    ->searchable()
                    ->copyable()
                    ->copyMessage(__('admin.system_settings.key_copied'))
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.system_settings.name'))
                    ->sortable()
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('admin.system_settings.type'))
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'string', 'text' => 'gray',
                        'number' => 'blue',
                        'boolean' => 'green',
                        'array', 'json' => 'purple',
                        'file', 'image' => 'orange',
                        'select' => 'indigo',
                        'color' => 'pink',
                        'date', 'datetime' => 'yellow',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('group')
                    ->label(__('admin.system_settings.group'))
                    ->sortable()
                    ->badge()
                    ->color('secondary'),
                Tables\Columns\TextColumn::make('value')
                    ->label(__('admin.system_settings.value'))
                    ->formatStateUsing(fn (SystemSetting $record) => $record->getFormattedValue())
                    ->limit(50)
                    ->tooltip(function (SystemSetting $record) {
                        return $record->getFormattedValue();
                    }),
                Tables\Columns\IconColumn::make('is_public')
                    ->label(__('admin.system_settings.is_public'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_required')
                    ->label(__('admin.system_settings.is_required'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_encrypted')
                    ->label(__('admin.system_settings.is_encrypted'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_readonly')
                    ->label(__('admin.system_settings.is_readonly'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.system_settings.is_active'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('admin.system_settings.sort_order'))
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('updated_by')
                    ->label(__('admin.system_settings.updated_by'))
                    ->formatStateUsing(fn ($state) => $state ? \App\Models\User::find($state)?->name : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.system_settings.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('environment')
                    ->label(__('admin.system_settings.environment'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'production' => 'danger',
                        'staging' => 'warning',
                        'development' => 'info',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tags')
                    ->label(__('admin.system_settings.tags'))
                    ->badge()
                    ->separator(',')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('version')
                    ->label(__('admin.system_settings.version'))
                    ->badge()
                    ->color('secondary')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('access_count')
                    ->label(__('admin.system_settings.access_count'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('last_accessed_at')
                    ->label(__('admin.system_settings.last_accessed_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label(__('admin.system_settings.category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('admin.system_settings.type'))
                    ->options([
                        'string' => 'String',
                        'text' => 'Text',
                        'number' => 'Number',
                        'boolean' => 'Boolean',
                        'array' => 'Array',
                        'json' => 'JSON',
                        'file' => 'File',
                        'image' => 'Image',
                        'select' => 'Select',
                        'color' => 'Color',
                        'date' => 'Date',
                        'datetime' => 'DateTime',
                    ]),
                Tables\Filters\SelectFilter::make('group')
                    ->label(__('admin.system_settings.group'))
                    ->options([
                        'general' => 'General',
                        'ecommerce' => 'E-commerce',
                        'email' => 'Email',
                        'payment' => 'Payment',
                        'shipping' => 'Shipping',
                        'seo' => 'SEO',
                        'security' => 'Security',
                        'api' => 'API',
                        'appearance' => 'Appearance',
                        'notifications' => 'Notifications',
                    ]),
                Tables\Filters\TernaryFilter::make('is_public')
                    ->label(__('admin.system_settings.is_public')),
                Tables\Filters\TernaryFilter::make('is_required')
                    ->label(__('admin.system_settings.is_required')),
                Tables\Filters\TernaryFilter::make('is_encrypted')
                    ->label(__('admin.system_settings.is_encrypted')),
                Tables\Filters\TernaryFilter::make('is_readonly')
                    ->label(__('admin.system_settings.is_readonly')),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('admin.system_settings.is_active')),
                Tables\Filters\SelectFilter::make('environment')
                    ->label(__('admin.system_settings.environment'))
                    ->options([
                        'all' => __('admin.system_settings.all_environments'),
                        'production' => __('admin.system_settings.production'),
                        'staging' => __('admin.system_settings.staging'),
                        'development' => __('admin.system_settings.development'),
                    ]),
                Tables\Filters\TernaryFilter::make('is_cacheable')
                    ->label(__('admin.system_settings.is_cacheable')),
                Tables\Filters\Filter::make('has_dependencies')
                    ->label(__('admin.system_settings.has_dependencies'))
                    ->query(fn (Builder $query): Builder => $query->whereHas('dependencies')),
                Tables\Filters\Filter::make('has_dependents')
                    ->label(__('admin.system_settings.has_dependents'))
                    ->query(fn (Builder $query): Builder => $query->whereHas('dependents')),
                Tables\Filters\Filter::make('recently_updated')
                    ->label(__('admin.system_settings.recently_updated'))
                    ->query(fn (Builder $query): Builder => $query->where('updated_at', '>=', now()->subDays(7))),
                Tables\Filters\Filter::make('frequently_accessed')
                    ->label(__('admin.system_settings.frequently_accessed'))
                    ->query(fn (Builder $query): Builder => $query->where('access_count', '>', 10)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (SystemSetting $record) => $record->canBeModified()),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (SystemSetting $record) => $record->canBeModified()),
                Tables\Actions\Action::make('view_history')
                    ->label(__('admin.system_settings.view_history'))
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->url(fn (SystemSetting $record) => route('filament.admin.resources.system-settings.history', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('view_dependencies')
                    ->label(__('admin.system_settings.view_dependencies'))
                    ->icon('heroicon-o-link')
                    ->color('warning')
                    ->visible(fn (SystemSetting $record) => $record->hasDependencies() || $record->hasDependents())
                    ->url(fn (SystemSetting $record) => route('filament.admin.resources.system-settings.dependencies', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('duplicate')
                    ->label(__('admin.system_settings.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->action(function (SystemSetting $record) {
                        $newRecord = $record->replicate();
                        $newRecord->key = $record->key.'_copy_'.time();
                        $newRecord->name = $record->name.' (Copy)';
                        $newRecord->save();

                        return redirect()->route('filament.admin.resources.system-settings.edit', $newRecord);
                    }),
                Tables\Actions\Action::make('export')
                    ->label(__('admin.system_settings.export'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (SystemSetting $record) {
                        $data = [
                            'key' => $record->key,
                            'name' => $record->name,
                            'value' => $record->value,
                            'type' => $record->type,
                            'group' => $record->group,
                            'description' => $record->description,
                            'help_text' => $record->help_text,
                            'validation_rules' => $record->validation_rules,
                            'options' => $record->options,
                            'default_value' => $record->default_value,
                        ];

                        $filename = 'system_setting_'.$record->key.'_'.now()->format('Y-m-d_H-i-s').'.json';

                        return response()
                            ->json($data)
                            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->hasRole('admin')),
                    Tables\Actions\BulkAction::make('activate')
                        ->label(__('admin.system_settings.activate_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_active' => true]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label(__('admin.system_settings.deactivate_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_active' => false]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('export')
                        ->label(__('admin.system_settings.export_selected'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function ($records) {
                            $data = $records->map(function ($record) {
                                return [
                                    'key' => $record->key,
                                    'name' => $record->name,
                                    'value' => $record->value,
                                    'type' => $record->type,
                                    'group' => $record->group,
                                    'description' => $record->description,
                                    'help_text' => $record->help_text,
                                    'validation_rules' => $record->validation_rules,
                                    'options' => $record->options,
                                    'default_value' => $record->default_value,
                                ];
                            });

                            $filename = 'system_settings_export_'.now()->format('Y-m-d_H-i-s').'.json';

                            return response()
                                ->json($data->toArray())
                                ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
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
            'index' => Pages\ListSystemSettings::route('/'),
            'create' => Pages\CreateSystemSetting::route('/create'),
            'view' => Pages\ViewSystemSetting::route('/{record}'),
            'edit' => Pages\EditSystemSetting::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.system_settings.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.system_settings.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.system_settings.plural_model_label');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->name;
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('admin.system_settings.category') => $record->category?->name,
            __('admin.system_settings.key') => $record->key,
            __('admin.system_settings.type') => $record->type,
            __('admin.system_settings.group') => $record->group,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['key', 'name', 'description'];
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\SystemSettingsOverviewWidget::class,
            \App\Filament\Widgets\SystemSettingsByGroupChartWidget::class,
            \App\Filament\Widgets\SystemSettingsRecentActivityWidget::class,
            \App\Filament\Widgets\SystemSettingsByCategoryWidget::class,
        ];
    }
}
