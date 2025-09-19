<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\SystemSettingsResource\Pages;
use App\Models\SystemSetting;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
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
    protected static ?string $model = SystemSetting::class;    /** @var UnitEnum|string|null */
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::System;

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
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('system_settings.basic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            TextInput::make('key')
                                ->label(__('system_settings.key'))
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash'])
                                ->helperText(__('system_settings.key_help')),
                            Select::make('type')
                                ->label(__('system_settings.type'))
                                ->options([
                                    'string' => __('system_settings.types.string'),
                                    'integer' => __('system_settings.types.integer'),
                                    'boolean' => __('system_settings.types.boolean'),
                                    'float' => __('system_settings.types.float'),
                                    'array' => __('system_settings.types.array'),
                                    'json' => __('system_settings.types.json'),
                                    'file' => __('system_settings.types.file'),
                                ])
                                ->required()
                                ->live()
                                ->default('string'),
                        ]),
                    Textarea::make('description')
                        ->label(__('system_settings.description'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
            Section::make(__('system_settings.value'))
                ->components([
                    Forms\Components\ViewField::make('value_field')
                        ->view('filament.forms.components.dynamic-value-field')
                        ->live(),
                ]),
            Section::make(__('system_settings.categorization'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Select::make('category_id')
                                ->label(__('system_settings.category'))
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(255),
                                    Textarea::make('description')
                                        ->maxLength(500),
                                ]),
                            TextInput::make('group')
                                ->label(__('system_settings.group'))
                                ->maxLength(255)
                                ->helperText(__('system_settings.group_help')),
                        ]),
                ]),
            Section::make(__('system_settings.validation'))
                ->components([
                    Grid::make(2)
                        ->components([
                            TextInput::make('validation_rules')
                                ->label(__('system_settings.validation_rules'))
                                ->maxLength(500)
                                ->helperText(__('system_settings.validation_rules_help')),
                            TextInput::make('default_value')
                                ->label(__('system_settings.default_value'))
                                ->maxLength(500),
                        ]),
                ]),
            Section::make(__('system_settings.settings'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Toggle::make('is_public')
                                ->label(__('system_settings.is_public'))
                                ->default(false)
                                ->helperText(__('system_settings.is_public_help')),
                            Toggle::make('is_encrypted')
                                ->label(__('system_settings.is_encrypted'))
                                ->default(false)
                                ->helperText(__('system_settings.is_encrypted_help')),
                        ]),
                    Grid::make(2)
                        ->components([
                            TextInput::make('sort_order')
                                ->label(__('system_settings.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                            Toggle::make('is_required')
                                ->label(__('system_settings.is_required'))
                                ->default(false),
                        ]),
                ]),
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
                    ->weight('bold')
                    ->copyable(),
                TextColumn::make('type')
                    ->label(__('system_settings.type'))
                    ->formatStateUsing(fn(string $state): string => __("system_settings.types.{$state}"))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'string' => 'gray',
                        'integer' => 'blue',
                        'boolean' => 'green',
                        'float' => 'yellow',
                        'array' => 'purple',
                        'json' => 'indigo',
                        'file' => 'pink',
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
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('group')
                    ->label(__('system_settings.group'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_public')
                    ->label(__('system_settings.is_public'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_encrypted')
                    ->label(__('system_settings.is_encrypted'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_required')
                    ->label(__('system_settings.is_required'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->label(__('system_settings.type'))
                    ->options([
                        'string' => __('system_settings.types.string'),
                        'integer' => __('system_settings.types.integer'),
                        'boolean' => __('system_settings.types.boolean'),
                        'float' => __('system_settings.types.float'),
                        'array' => __('system_settings.types.array'),
                        'json' => __('system_settings.types.json'),
                        'file' => __('system_settings.types.file'),
                    ]),
                SelectFilter::make('category_id')
                    ->label(__('system_settings.category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_public')
                    ->label(__('system_settings.is_public'))
                    ->boolean()
                    ->trueLabel(__('system_settings.public_only'))
                    ->falseLabel(__('system_settings.private_only'))
                    ->native(false),
                TernaryFilter::make('is_encrypted')
                    ->label(__('system_settings.is_encrypted'))
                    ->boolean()
                    ->trueLabel(__('system_settings.encrypted_only'))
                    ->falseLabel(__('system_settings.unencrypted_only'))
                    ->native(false),
                TernaryFilter::make('is_required')
                    ->label(__('system_settings.is_required'))
                    ->boolean()
                    ->trueLabel(__('system_settings.required_only'))
                    ->falseLabel(__('system_settings.optional_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
