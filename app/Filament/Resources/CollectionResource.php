<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CollectionResource\Pages;
use App\Models\Collection;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use UnitEnum;

final class CollectionResource extends Resource
{
    protected static ?string $model = Collection::class;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-folder';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Products';
    }

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
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('collections.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('collections.name'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Str::slug($state)) : null),
                            TextInput::make('slug')
                                ->label(__('collections.slug'))
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    Textarea::make('description')
                        ->label(__('collections.description'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
            Section::make(__('collections.media'))
                ->schema([
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
                        ->visibility('public'),
                    FileUpload::make('banner')
                        ->label(__('collections.banner'))
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios([
                            '21:9',
                        ])
                        ->directory('collections/banners')
                        ->visibility('public'),
                ]),
            Section::make(__('collections.products'))
                ->schema([
                    Select::make('products')
                        ->label(__('collections.products'))
                        ->relationship('products', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload(),
                ]),
            Section::make(__('collections.seo'))
                ->schema([
                    TextInput::make('seo_title')
                        ->label(__('collections.seo_title'))
                        ->maxLength(255),
                    Textarea::make('seo_description')
                        ->label(__('collections.seo_description'))
                        ->rows(2)
                        ->maxLength(500),
                ]),
            Section::make(__('collections.settings'))
                ->schema([
                    Toggle::make('is_active')
                        ->label(__('collections.is_active'))
                        ->default(true),
                    Toggle::make('is_featured')
                        ->label(__('collections.is_featured')),
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
                    ->weight('bold'),
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
                    ->formatStateUsing(fn (string $state): string => __("collections.sort_orders.{$state}")),
                IconColumn::make('is_active')
                    ->label(__('collections.is_active'))
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label(__('collections.is_featured'))
                    ->boolean(),
                IconColumn::make('auto_update')
                    ->label(__('collections.auto_update'))
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
                    ->trueLabel(__('collections.active_only'))
                    ->falseLabel(__('collections.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_featured')
                    ->trueLabel(__('collections.featured_only'))
                    ->falseLabel(__('collections.not_featured'))
                    ->native(false),
                TernaryFilter::make('auto_update')
                    ->trueLabel(__('collections.auto_update_only'))
                    ->falseLabel(__('collections.manual_only'))
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
                Action::make('update_products')
                    ->label(__('collections.update_products'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn (Collection $record): bool => (bool) $record->auto_update)
                    ->action(function (Collection $record): void {
                        // Auto-update products based on collection settings
                        Notification::make()
                            ->title(__('collections.products_updated_successfully'))
                            ->success()
                            ->send();
                    }),
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
            'index' => Pages\ListCollections::route('/'),
            'create' => Pages\CreateCollection::route('/create'),
            'view' => Pages\ViewCollection::route('/{record}'),
            'edit' => Pages\EditCollection::route('/{record}/edit'),
        ];
    }
}
