<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\SystemSettingsResource\Pages;
use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class SystemSettingsResource extends Resource
{
    protected static ?string $model = SystemSetting::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    /** @var string|\BackedEnum|null */
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::System;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'System Settings';

    protected static ?string $modelLabel = 'System Setting';

    protected static ?string $pluralModelLabel = 'System Settings';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make(__('admin.system_settings.setting_information'))
                    ->components([
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
                    ])
                    ->columns(2),

                Forms\Components\Section::make(__('admin.system_settings.value_configuration'))
                    ->components([
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
                                : ['application/pdf', 'text/plain', 'application/json']
                            )
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
                    ->components([
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
                    ->color(fn ($record) => $record->category?->color ?? 'primary')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('key')
                    ->label(__('admin.system_settings.key'))
                    ->sortable()
                    ->searchable()
                    ->copyable()
                    ->copyMessage(__('admin.system_settings.key_copied'))
                    ->copyMessageDuration(1500)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.system_settings.name'))
                    ->sortable()
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (SystemSetting $record) {
                        return $record->name;
                    }),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('admin.system_settings.type'))
                    ->sortable()
                    ->badge()
                    ->icon(fn (SystemSetting $record) => $record->getIconForType())
                    ->color(fn (SystemSetting $record) => $record->getColorForType())
                    ->toggleable(),

                Tables\Columns\TextColumn::make('group')
                    ->label(__('admin.system_settings.group'))
                    ->sortable()
                    ->badge()
                    ->color('secondary')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('value')
                    ->label(__('admin.system_settings.value'))
                    ->formatStateUsing(fn (SystemSetting $record) => $record->getDisplayValue())
                    ->limit(50)
                    ->tooltip(function (SystemSetting $record) {
                        return $record->getFormattedValue();
                    })
                    ->html()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.system_settings.status'))
                    ->formatStateUsing(fn (SystemSetting $record) => $record->getBadgeForStatus())
                    ->badge()
                    ->color(fn (SystemSetting $record) => $record->is_active ? 'success' : 'danger')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_public')
                    ->label(__('admin.system_settings.is_public'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_required')
                    ->label(__('admin.system_settings.is_required'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_encrypted')
                    ->label(__('admin.system_settings.is_encrypted'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_readonly')
                    ->label(__('admin.system_settings.is_readonly'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.system_settings.is_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('admin.system_settings.sort_order'))
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_by')
                    ->label(__('admin.system_settings.updated_by'))
                    ->formatStateUsing(fn ($state) => $state ? \App\Models\User::find($state)?->name : '-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.system_settings.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label(__('admin.system_settings.category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\SelectFilter::make('type')
                    ->label(__('admin.system_settings.type'))
                    ->options([
                        'string' => __('admin.system_settings.string'),
                        'text' => __('admin.system_settings.text'),
                        'number' => __('admin.system_settings.number'),
                        'boolean' => __('admin.system_settings.boolean'),
                        'array' => __('admin.system_settings.array'),
                        'json' => __('admin.system_settings.json'),
                        'file' => __('admin.system_settings.file'),
                        'image' => __('admin.system_settings.image'),
                        'select' => __('admin.system_settings.select'),
                        'color' => __('admin.system_settings.color'),
                        'date' => __('admin.system_settings.date'),
                        'datetime' => __('admin.system_settings.datetime'),
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('group')
                    ->label(__('admin.system_settings.group'))
                    ->options([
                        'general' => __('admin.system_settings.general'),
                        'ecommerce' => __('admin.system_settings.ecommerce'),
                        'email' => __('admin.system_settings.email'),
                        'payment' => __('admin.system_settings.payment'),
                        'shipping' => __('admin.system_settings.shipping'),
                        'seo' => __('admin.system_settings.seo'),
                        'security' => __('admin.system_settings.security'),
                        'api' => __('admin.system_settings.api'),
                        'appearance' => __('admin.system_settings.appearance'),
                        'notifications' => __('admin.system_settings.notifications'),
                    ])
                    ->multiple(),

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

                Tables\Filters\Filter::make('has_dependencies')
                    ->label(__('admin.system_settings.has_dependencies'))
                    ->query(fn (Builder $query): Builder => $query->whereHas('dependencies')),

                Tables\Filters\Filter::make('has_dependents')
                    ->label(__('admin.system_settings.has_dependents'))
                    ->query(fn (Builder $query): Builder => $query->whereHas('dependents')),

                Tables\Filters\Filter::make('recent_changes')
                    ->label(__('admin.system_settings.recent_changes'))
                    ->query(fn (Builder $query): Builder => $query->where('updated_at', '>=', now()->subDays(7))),

                Tables\Filters\Filter::make('no_category')
                    ->label(__('admin.system_settings.no_category'))
                    ->query(fn (Builder $query): Builder => $query->whereNull('category_id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (SystemSetting $record) => $record->canBeModified()),
                Tables\Actions\Action::make('reset_to_default')
                    ->label(__('admin.system_settings.reset_to_default'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (SystemSetting $record) => $record->canBeModified() && $record->default_value !== null)
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.system_settings.reset_to_default_confirm'))
                    ->modalDescription(__('admin.system_settings.reset_to_default_description'))
                    ->action(function (SystemSetting $record) {
                        $record->update(['value' => $record->default_value]);
                        \Filament\Notifications\Notification::make()
                            ->title(__('admin.system_settings.reset_success'))
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('copy_key')
                    ->label(__('admin.system_settings.copy_key'))
                    ->icon('heroicon-o-clipboard')
                    ->color('gray')
                    ->action(function (SystemSetting $record) {
                        return $record->key;
                    })
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->title(__('admin.system_settings.key_copied'))
                            ->success()
                    ),
                Tables\Actions\Action::make('view_history')
                    ->label(__('admin.system_settings.view_history'))
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->url(fn (SystemSetting $record): string => route('filament.admin.resources.system-settings.view', $record) . '?tab=history'),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (SystemSetting $record) => $record->canBeModified()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label(__('admin.system_settings.activate'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);
                            \Filament\Notifications\Notification::make()
                                ->title(__('admin.system_settings.settings_activated'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label(__('admin.system_settings.deactivate'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);
                            \Filament\Notifications\Notification::make()
                                ->title(__('admin.system_settings.settings_deactivated'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('make_public')
                        ->label(__('admin.system_settings.make_public'))
                        ->icon('heroicon-o-globe-alt')
                        ->color('info')
                        ->action(function ($records) {
                            $records->each->update(['is_public' => true]);
                            \Filament\Notifications\Notification::make()
                                ->title(__('admin.system_settings.settings_made_public'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('make_private')
                        ->label(__('admin.system_settings.make_private'))
                        ->icon('heroicon-o-lock-closed')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each->update(['is_public' => false]);
                            \Filament\Notifications\Notification::make()
                                ->title(__('admin.system_settings.settings_made_private'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('export')
                        ->label(__('admin.system_settings.export_settings'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
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
                                    'is_public' => $record->is_public,
                                    'is_required' => $record->is_required,
                                    'is_encrypted' => $record->is_encrypted,
                                    'is_readonly' => $record->is_readonly,
                                    'validation_rules' => $record->validation_rules,
                                    'options' => $record->options,
                                    'default_value' => $record->default_value,
                                    'sort_order' => $record->sort_order,
                                    'is_active' => $record->is_active,
                                ];
                            });

                            $filename = 'system_settings_export_' . now()->format('Y-m-d_H-i-s') . '.json';
                            
                            return response()->streamDownload(function () use ($data) {
                                echo json_encode($data, JSON_PRETTY_PRINT);
                            }, $filename);
                        }),
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->hasRole('admin')),
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
}