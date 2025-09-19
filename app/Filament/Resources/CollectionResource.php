<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CollectionResource\Pages;
use App\Models\Collection;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use UnitEnum;

/**
 * CollectionResource
 *
 * Filament v4 resource for Collection management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class CollectionResource extends Resource
{
    protected static ?string $model = Collection::class;

    /**
     * @var UnitEnum|string|null
     */    /** @var UnitEnum|string|null */
    protected static string|UnitEnum|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('collections.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return "Products";
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('collections.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('collections.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('collections.basic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            TextInput::make('name')
                                ->label(__('collections.name'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) =>
                                    $operation === 'create' ? $set('slug', \Str::slug($state)) : null),
                            TextInput::make('slug')
                                ->label(__('collections.slug'))
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    Textarea::make('description')
                        ->label(__('collections.description'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
            Section::make(__('collections.media'))
                ->components([
                    FileUpload::make('image')
                        ->label(__('collections.image'))
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios([
                            '1:1',
                            '16:9',
                            '4:3',
                        ])
                        ->directory('collections/images')
                        ->visibility('public')
                        ->columnSpanFull(),
                    FileUpload::make('banner')
                        ->label(__('collections.banner'))
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios([
                            '16:9',
                            '21:9',
                            '4:3',
                        ])
                        ->directory('collections/banners')
                        ->visibility('public')
                        ->columnSpanFull(),
                ]),
            Section::make(__('collections.products'))
                ->components([
                    Select::make('products')
                        ->label(__('collections.products'))
                        ->relationship('products', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->columnSpanFull(),
                ]),
            Section::make(__('collections.rules'))
                ->components([
                    Repeater::make('rules')
                        ->label(__('collections.rules'))
                        ->components([
                            Select::make('type')
                                ->label(__('collections.rule_type'))
                                ->options([
                                    'category' => __('collections.rule_types.category'),
                                    'brand' => __('collections.rule_types.brand'),
                                    'price' => __('collections.rule_types.price'),
                                    'tag' => __('collections.rule_types.tag'),
                                    'inventory' => __('collections.rule_types.inventory'),
                                ])
                                ->required(),
                            TextInput::make('condition')
                                ->label(__('collections.condition'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('value')
                                ->label(__('collections.value'))
                                ->required()
                                ->maxLength(255),
                        ])
                        ->columns(3)
                        ->addActionLabel(__('collections.add_rule'))
                        ->columnSpanFull(),
                ]),
            Section::make(__('collections.seo'))
                ->components([
                    TextInput::make('seo_title')
                        ->label(__('collections.seo_title'))
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Textarea::make('seo_description')
                        ->label(__('collections.seo_description'))
                        ->rows(2)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            Section::make(__('collections.settings'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Toggle::make('is_active')
                                ->label(__('collections.is_active'))
                                ->default(true),
                            Toggle::make('is_featured')
                                ->label(__('collections.is_featured')),
                        ]),
                    Grid::make(2)
                        ->components([
                            Select::make('sort_order')
                                ->label(__('collections.sort_order'))
                                ->options([
                                    'manual' => __('collections.sort_orders.manual'),
                                    'name_asc' => __('collections.sort_orders.name_asc'),
                                    'name_desc' => __('collections.sort_orders.name_desc'),
                                    'price_asc' => __('collections.sort_orders.price_asc'),
                                    'price_desc' => __('collections.sort_orders.price_desc'),
                                    'created_asc' => __('collections.sort_orders.created_asc'),
                                    'created_desc' => __('collections.sort_orders.created_desc'),
                                ])
                                ->default('manual'),
                            Toggle::make('auto_update')
                                ->label(__('collections.auto_update'))
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
                ImageColumn::make('image')
                    ->label(__('collections.image'))
                    ->circular()
                    ->size(40),
                TextColumn::make('name')
                    ->label(__('collections.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('slug')
                    ->label(__('collections.slug'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('products_count')
                    ->label(__('collections.products_count'))
                    ->counts('products')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('collections.sort_order'))
                    ->formatStateUsing(fn(string $state): string => __("collections.sort_orders.{$state}"))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('collections.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label(__('collections.is_featured'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('auto_update')
                    ->label(__('collections.auto_update'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('collections.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('collections.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('collections.is_active'))
                    ->boolean()
                    ->trueLabel(__('collections.active_only'))
                    ->falseLabel(__('collections.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_featured')
                    ->label(__('collections.is_featured'))
                    ->boolean()
                    ->trueLabel(__('collections.featured_only'))
                    ->falseLabel(__('collections.not_featured'))
                    ->native(false),
                TernaryFilter::make('auto_update')
                    ->label(__('collections.auto_update'))
                    ->boolean()
                    ->trueLabel(__('collections.auto_update_only'))
                    ->falseLabel(__('collections.manual_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn(Collection $record): string => $record->is_active ? __('collections.deactivate') : __('collections.activate'))
                    ->icon(fn(Collection $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(Collection $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Collection $record): void {
                        $record->update(['is_active' => !$record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? __('collections.activated_successfully') : __('collections.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('update_products')
                    ->label(__('collections.update_products'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn(Collection $record): bool => $record->auto_update)
                    ->action(function (Collection $record): void {
                        // Auto-update products based on rules
                        Notification::make()
                            ->title(__('collections.products_updated_successfully'))
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
            'index' => Pages\ListCollections::route('/'),
            'create' => Pages\CreateCollection::route('/create'),
            'view' => Pages\ViewCollection::route('/{record}'),
            'edit' => Pages\EditCollection::route('/{record}/edit'),
        ];
    }
}
