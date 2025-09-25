<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ProductComparisonResource\Pages;
use App\Models\ProductComparison;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * ProductComparisonResource
 *
 * Filament v4 resource for ProductComparison management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class ProductComparisonResource extends Resource
{
    protected static ?string $slug = 'product-comparisons';

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return NavigationGroup::Products;
    }

    protected static ?string $model = ProductComparison::class;

    protected static ?int $navigationSort = 15;

    protected static ?string $recordTitleAttribute = 'session_id';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     */
    public static function getNavigationLabel(): string
    {
        return __('product_comparisons.title');
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('product_comparisons.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('product_comparisons.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaSection::make(__('product_comparisons.basic_information'))
                ->components([
                    Grid::make(2)->components([
                        Select::make('user_id')
                            ->label(__('product_comparisons.user'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('product_id')
                            ->label(__('product_comparisons.product'))
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                    TextInput::make('session_id')
                        ->label(__('product_comparisons.session_id'))
                        ->maxLength(255)
                        ->helperText(__('product_comparisons.session_id_help')),
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
                TextColumn::make('user.name')
                    ->label(__('product_comparisons.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label(__('product_comparisons.product'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('product.sku')
                    ->label(__('product_comparisons.product_sku'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('session_id')
                    ->label(__('product_comparisons.session_id'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('product_comparisons.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('product_comparisons.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label(__('product_comparisons.user'))
                    ->relationship('user', 'name')
                    ->preload(),
                SelectFilter::make('product_id')
                    ->label(__('product_comparisons.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label(__('product_comparisons.created_from')),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label(__('product_comparisons.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListProductComparisons::route('/'),
            'create' => Pages\CreateProductComparison::route('/create'),
            'view' => Pages\ViewProductComparison::route('/{record}'),
            'edit' => Pages\EditProductComparison::route('/{record}/edit'),
        ];
    }
}
