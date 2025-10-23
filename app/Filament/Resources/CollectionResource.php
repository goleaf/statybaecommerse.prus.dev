<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Components\Combobox;
use App\Filament\Resources\CollectionResource\Pages;
use App\Models\Collection;
use BackedEnum;
use Novadaemon\FilamentCombobox\Combobox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;
use UnitEnum;
use Filament\Schemas\Schema;

final class CollectionResource extends Resource
{
    use HasNav;

    protected static ?string $model = Collection::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    protected static UnitEnum|string|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 2;

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('collections.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('collections.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Form $form): Form
    {
        return $form->components([
            Section::make(__('collections.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('collections.name'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                            TextInput::make('slug')
                                ->label(__('collections.slug'))
                                ->unique(table: Collection::class, column: 'slug', ignoreRecord: true)
                                ->rules(['alpha_dash'])
                                ->helperText(__('collections.help.slug')),
                        ]),
                    Textarea::make('description')
                        ->label(__('collections.description'))
                        ->rows(4)
                        ->columnSpanFull(),
                ])
                ->columns(1),
            Section::make(__('collections.display_type'))
                ->components([
                    Grid::make(3)
                        ->components([
                            Select::make('display_type')
                                ->label(__('collections.display_type'))
                                ->options([
                                    'grid'     => __('collections.display_types.grid'),
                                    'list'     => __('collections.display_types.list'),
                                    'carousel' => __('collections.display_types.carousel'),
                                ])
                                ->default('grid')
                                ->required(),
                            TextInput::make('products_per_page')
                                ->label(__('collections.products_per_page'))
                                ->numeric()
                                ->minValue(1)
                                ->default(12),
                            Toggle::make('show_filters')
                                ->label(__('collections.show_filters'))
                                ->default(true),
                        ]),
                ]),
            Section::make(__('collections.business_info'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Toggle::make('is_visible')
                                ->label(__('collections.is_visible'))
                                ->default(true),
                            Toggle::make('is_active')
                                ->label(__('collections.is_active'))
                                ->default(true),
                            Toggle::make('is_automatic')
                                ->label(__('collections.is_automatic'))
                                ->default(false),
                        ]),
                    Grid::make(3)
                        ->schema([
                            TextInput::make('sort_order')
                                ->label(__('collections.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->helperText(__('collections.help.sort_order')),
                            TextInput::make('max_products')
                                ->label(__('collections.max_products'))
                                ->numeric()
                                ->minValue(0)
                                ->helperText(__('collections.help.max_products')),
                            Textarea::make('rules')
                                ->label(__('collections.rules'))
                                ->rows(3)
                                ->columnSpanFull()
                                ->helperText(__('collections.help.rules')),
                        ])
                        ->columnSpanFull(),
                ])
                ->columns(1),
            Section::make(__('collections.display_type'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Select::make('display_type')
                                ->label(__('collections.display_type'))
                                ->options([
                                    'grid'     => __('collections.display_types.grid'),
                                    'list'     => __('collections.display_types.list'),
                                    'carousel' => __('collections.display_types.carousel'),
                                ])
                                ->default('grid')
                                ->required(),
                            TextInput::make('products_per_page')
                                ->label(__('collections.products_per_page'))
                                ->numeric()
                                ->minValue(1)
                                ->default(12),
                            Toggle::make('show_filters')
                                ->label(__('collections.show_filters'))
                                ->default(true),
                        ]),
                ]),
            Section::make(__('collections.media'))
                ->schema([
                    SpatieMediaLibraryFileUpload::make('images')
                        ->collection('images')
                        ->label(__('collections.image'))
                        ->image()
                        ->imageEditor()
                        ->maxFiles(1),
                    SpatieMediaLibraryFileUpload::make('banner')
                        ->collection('banner')
                        ->label(__('collections.banner'))
                        ->image()
                        ->imageEditor()
                        ->maxFiles(1),
                ])
                ->columns(2),
            Section::make(__('collections.collection_info'))
                ->components([
                    Combobox::make('products')
                        ->label(__('translations.products'))
                        ->relationship('products', 'name')
                        ->multiple()
                        ->searchable()
                        ->boxSearchs()
                        ->height('350px')
                        ->preload(),
                ]),
            Section::make(__('collections.seo_info'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('seo_title')
                                ->label(__('collections.seo_title'))
                                ->maxLength(255),
                            TextInput::make('meta_title')
                                ->label(__('collections.meta_title'))
                                ->maxLength(255),
                            Textarea::make('seo_description')
                                ->label(__('collections.seo_description'))
                                ->rows(2)
                                ->columnSpan(2),
                            Textarea::make('meta_description')
                                ->label(__('collections.meta_description'))
                                ->rows(2)
                                ->columnSpan(2),
                            TextInput::make('meta_keywords')
                                ->label(__('collections.meta_keywords'))
                                ->helperText(__('collections.help.meta_keywords')),
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
            ->columns([
                ImageColumn::make('image')
                    ->label(__('collections.image'))
                    ->circular()
                    ->size(40),
                TextColumn::make('name')
                    ->label(__('collections.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->formatStateUsing(fn (?string $state, Collection $record): ?string => $record->getTranslatedName(app()->getLocale()) ?? $state),
                TextColumn::make('slug')
                    ->label(__('collections.slug'))
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('products_count')
                    ->label(__('collections.products_count'))
                    ->counts('products')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('collections.sort_order'))
                    ->sortable(),
                TextColumn::make('display_type')
                    ->label(__('collections.display_type'))
                    ->formatStateUsing(fn (?string $state): string => $state ? __('collections.display_types.' . $state) : '-'),
                IconColumn::make('is_visible')
                    ->label(__('collections.is_visible'))
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label(__('collections.is_active'))
                    ->boolean(),
                IconColumn::make('is_automatic')
                    ->label(__('collections.is_automatic'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('collections.created_at'))
                    ->dateTime(),
                TextColumn::make('updated_at')
                    ->label(__('collections.updated_at'))
                    ->dateTime(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->native(false),
                TernaryFilter::make('is_visible')
                    ->native(false),
                TernaryFilter::make('is_automatic')
                    ->native(false),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn (Collection $record): string => $record->is_active ? __('collections.deactivate') : __('collections.activate'))
                    ->icon(fn (Collection $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Collection $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Collection $record): void {
                        $record->update(['is_active' => ! $record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? __('collections.activated_successfully') : __('collections.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('collections.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (EloquentCollection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('collections.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('collections.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (EloquentCollection $records): void {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('collections.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('name');
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
            'index'  => Pages\ListCollections::route('/'),
            'create' => Pages\CreateCollection::route('/create'),
            'view'   => Pages\ViewCollection::route('/{record}'),
            'edit'   => Pages\EditCollection::route('/{record}/edit'),
        ];
    }
}
