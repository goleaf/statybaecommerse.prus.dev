<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AttributeResource\Pages;
use App\Models\Attribute;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
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
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

final class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-tag';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Products';
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('attributes.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('attributes.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('attributes.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('attributes.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('slug')
                                ->label(__('attributes.slug'))
                                ->maxLength(255)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    Textarea::make('description')
                        ->label(__('attributes.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            Section::make(__('attributes.type_settings'))
                ->schema([
                    Select::make('type')
                        ->label(__('attributes.type'))
                        ->options([
                            'text' => __('attributes.types.text'),
                            'textarea' => __('attributes.types.textarea'),
                            'number' => __('attributes.types.number'),
                            'select' => __('attributes.types.select'),
                            'multiselect' => __('attributes.types.multiselect'),
                            'boolean' => __('attributes.types.boolean'),
                            'date' => __('attributes.types.date'),
                            'datetime' => __('attributes.types.datetime'),
                            'color' => __('attributes.types.color'),
                            'file' => __('attributes.types.file'),
                            'image' => __('attributes.types.image'),
                            'url' => __('attributes.types.url'),
                        ])
                        ->default('text')
                        ->live(),
                    Select::make('input_type')
                        ->label(__('attributes.input_type'))
                        ->options([
                            'text' => __('attributes.input_types.text'),
                            'textarea' => __('attributes.input_types.textarea'),
                            'number' => __('attributes.input_types.number'),
                            'email' => __('attributes.input_types.email'),
                            'url' => __('attributes.input_types.url'),
                            'tel' => __('attributes.input_types.tel'),
                            'password' => __('attributes.input_types.password'),
                            'search' => __('attributes.input_types.search'),
                        ])
                        ->default('text'),
                    Toggle::make('is_required')
                        ->label(__('attributes.is_required'))
                        ->default(false),
                    Toggle::make('is_filterable')
                        ->label(__('attributes.is_filterable'))
                        ->default(true),
                ]),
            Section::make(__('attributes.validation'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('min_length')
                                ->label(__('attributes.min_length'))
                                ->numeric()
                                ->minValue(0),
                            TextInput::make('max_length')
                                ->label(__('attributes.max_length'))
                                ->numeric(),
                            TextInput::make('min_value')
                                ->label(__('attributes.min_value'))
                                ->numeric(),
                            TextInput::make('max_value')
                                ->label(__('attributes.max_value'))
                                ->numeric(),
                        ]),
                    TextInput::make('validation_rules')
                        ->label(__('attributes.validation_rules'))
                        ->helperText(__('attributes.validation_rules_help'))
                        ->columnSpanFull(),
                ]),
            Section::make(__('attributes.options'))
                ->schema([
                    Repeater::make('options')
                        ->label(__('attributes.options'))
                        ->defaultItems(0)
                        ->schema([
                            TextInput::make('value')
                                ->label(__('attributes.option_value'))
                                ->required(),
                            TextInput::make('label')
                                ->label(__('attributes.option_label'))
                                ->required(),
                            TextInput::make('sort_order')
                                ->label(__('attributes.option_sort_order'))
                                ->numeric()
                                ->default(0),
                            Toggle::make('is_active')
                                ->label(__('attributes.option_is_active'))
                                ->default(true),
                        ])
                        ->columns(4)
                        ->addActionLabel(__('attributes.add_option')),
                ]),
            Section::make(__('attributes.settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('attributes.is_active'))
                                ->default(true),
                            Toggle::make('is_searchable')
                                ->label(__('attributes.is_searchable'))
                                ->default(true),
                            TextInput::make('sort_order')
                                ->label(__('attributes.sort_order'))
                                ->numeric()
                                ->default(0),
                            Select::make('group_name')
                                ->label(__('attributes.group'))
                                ->options([
                                    // Legacy / factory-generated group names
                                    'basic_info' => 'basic_info',
                                    'technical_specs' => 'technical_specs',
                                    'materials' => 'materials',
                                    'features' => 'features',
                                    'compatibility' => 'compatibility',
                                    'warranty' => 'warranty',
                                    // Current UI groups
                                    'general' => __('attributes.groups.general'),
                                    'technical' => __('attributes.groups.technical'),
                                    'appearance' => __('attributes.groups.appearance'),
                                    'dimensions' => __('attributes.groups.dimensions'),
                                    'shipping' => __('attributes.groups.shipping'),
                                    'seo' => __('attributes.groups.seo'),
                                    'other' => __('attributes.groups.other'),
                                ])
                                ->default('general'),
                        ]),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->query(\App\Models\Attribute::query()->withoutGlobalScopes())
            ->deferLoading(false)
            ->columns([
                TextColumn::make('name')
                    ->label(__('attributes.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('slug')
                    ->label(__('attributes.slug'))
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('type')
                    ->label(__('attributes.type'))
                    ->formatStateUsing(fn (string $state): string => __("attributes.types.{$state}"))
                    ->color(fn (string $state): string => match ($state) {
                        'text' => 'blue',
                        'number' => 'green',
                        'select' => 'purple',
                        'multiselect' => 'orange',
                        'boolean' => 'yellow',
                        'date' => 'pink',
                        'datetime' => 'indigo',
                        'color' => 'red',
                        'file' => 'gray',
                        'url' => 'teal',
                        default => 'gray',
                    }),
                TextColumn::make('group_name')
                    ->label(__('attributes.group'))
                    ->formatStateUsing(fn (string $state): string => __("attributes.groups.{$state}"))
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('options_count')
                    ->label(__('attributes.options_count'))
                    ->counts('values')
                    ->sortable(),
                IconColumn::make('is_required')
                    ->label(__('attributes.is_required'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_filterable')
                    ->label(__('attributes.is_filterable'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_searchable')
                    ->label(__('attributes.is_searchable'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('attributes.is_active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('attributes.sort_order'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('attributes.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('attributes.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'text' => __('attributes.types.text'),
                        'textarea' => __('attributes.types.textarea'),
                        'number' => __('attributes.types.number'),
                        'select' => __('attributes.types.select'),
                        'multiselect' => __('attributes.types.multiselect'),
                        'boolean' => __('attributes.types.boolean'),
                        'date' => __('attributes.types.date'),
                        'datetime' => __('attributes.types.datetime'),
                        'color' => __('attributes.types.color'),
                        'file' => __('attributes.types.file'),
                        'image' => __('attributes.types.image'),
                        'url' => __('attributes.types.url'),
                    ]),
                SelectFilter::make('group_name')
                    ->options([
                        'general' => __('attributes.groups.general'),
                        'technical' => __('attributes.groups.technical'),
                        'appearance' => __('attributes.groups.appearance'),
                        'dimensions' => __('attributes.groups.dimensions'),
                        'shipping' => __('attributes.groups.shipping'),
                        'seo' => __('attributes.groups.seo'),
                        'other' => __('attributes.groups.other'),
                    ]),
                TernaryFilter::make('is_required')
                    ->trueLabel(__('attributes.required_only'))
                    ->falseLabel(__('attributes.optional_only'))
                    ->native(false),
                TernaryFilter::make('is_filterable')
                    ->trueLabel(__('attributes.filterable_only'))
                    ->falseLabel(__('attributes.not_filterable'))
                    ->native(false),
                TernaryFilter::make('is_searchable')
                    ->trueLabel(__('attributes.searchable_only'))
                    ->falseLabel(__('attributes.not_searchable'))
                    ->native(false),
                TernaryFilter::make('is_active')
                    ->trueLabel(__('attributes.active_only'))
                    ->falseLabel(__('attributes.inactive_only'))
                    ->native(false),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make()
                    ->label(__('attributes.delete'))
                    ->action(function (Attribute $record): void {
                        $record->forceDelete();
                        Notification::make()
                            ->title(__('attributes.deleted_successfully'))
                            ->success()
                            ->send();
                    }),
                Actions\Action::make('toggle_active')
                    ->label(fn (Attribute $record): string => $record->is_active ? __('attributes.deactivate') : __('attributes.activate'))
                    ->icon(fn (Attribute $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Attribute $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Attribute $record): void {
                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? __('attributes.activated_successfully') : __('attributes.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()
                        ->action(function (Collection $records): void {
                            $records->each->forceDelete();
                            Notification::make()
                                ->title(__('attributes.bulk_deleted_success'))
                                ->success()
                                ->send();
                        }),
                    Actions\BulkAction::make('activate')
                        ->label(__('attributes.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('attributes.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    Actions\BulkAction::make('deactivate')
                        ->label(__('attributes.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('attributes.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    /**
     * Get the relations for this resource.
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttributes::route('/'),
            'create' => Pages\CreateAttribute::route('/create'),
            'view' => Pages\ViewAttribute::route('/{record}'),
            'edit' => Pages\EditAttribute::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }
}
