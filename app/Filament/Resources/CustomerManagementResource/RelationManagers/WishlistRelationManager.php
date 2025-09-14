<?php

declare (strict_types=1);
namespace App\Filament\Resources\CustomerManagementResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
/**
 * WishlistRelationManager
 * 
 * Filament v4 resource for WishlistRelationManager management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $relationship
 * @property string|null $title
 * @property string|null $modelLabel
 * @property string|null $pluralModelLabel
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class WishlistRelationManager extends RelationManager
{
    protected static string $relationship = 'wishlist';
    protected static ?string $title = 'admin.customers.wishlist';
    protected static ?string $modelLabel = 'admin.customers.wishlist_item';
    protected static ?string $pluralModelLabel = 'admin.customers.wishlist_items';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $schema->schema([Forms\Components\Select::make('product_id')->label(__('admin.customers.fields.product'))->relationship('product', 'name')->required()->searchable()->preload(), Forms\Components\Select::make('variant_id')->label(__('admin.customers.fields.variant'))->relationship('variant', 'name')->searchable()->preload(), Forms\Components\Textarea::make('notes')->label(__('admin.customers.fields.notes'))->rows(3), Forms\Components\DateTimePicker::make('added_at')->label(__('admin.customers.fields.added_at'))->required()]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('product.name')->columns([Tables\Columns\ImageColumn::make('product.image')->label(__('admin.customers.fields.image'))->circular()->placeholder('No image'), Tables\Columns\TextColumn::make('product.name')->label(__('admin.customers.fields.product'))->searchable()->sortable(), Tables\Columns\TextColumn::make('variant.name')->label(__('admin.customers.fields.variant'))->searchable()->placeholder(__('admin.no_variant')), Tables\Columns\TextColumn::make('product.price')->label(__('admin.customers.fields.price'))->money('EUR')->sortable(), Tables\Columns\TextColumn::make('product.stock_quantity')->label(__('admin.customers.fields.stock'))->numeric()->sortable()->formatStateUsing(function (?int $state): string {
            if ($state === null) {
                return __('admin.stock_status.unknown');
            }
            if ($state === 0) {
                return __('admin.out_of_stock');
            }
            if ($state < 10) {
                return __('admin.low_stock') . " ({$state})";
            }
            return __('admin.in_stock') . " ({$state})";
        }), Tables\Columns\TextColumn::make('notes')->label(__('admin.customers.fields.notes'))->searchable()->limit(50)->placeholder(__('admin.customers.no_notes')), Tables\Columns\TextColumn::make('added_at')->label(__('admin.customers.fields.added_at'))->dateTime('d/m/Y H:i')->sortable(), Tables\Columns\TextColumn::make('created_at')->label(__('admin.customers.fields.created_at'))->dateTime('d/m/Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([Tables\Filters\TernaryFilter::make('has_variant')->label(__('admin.customers.fields.has_variant'))->nullable()->trueLabel(__('admin.with_variant'))->falseLabel(__('admin.without_variant'))->queries(true: fn(Builder $query) => $query->whereNotNull('variant_id'), false: fn(Builder $query) => $query->whereNull('variant_id'), blank: fn(Builder $query) => $query), Tables\Filters\SelectFilter::make('stock_status')->label(__('admin.stock_status'))->options(['in_stock' => __('admin.in_stock'), 'low_stock' => __('admin.low_stock'), 'out_of_stock' => __('admin.out_of_stock')])->query(function (Builder $query, array $data): Builder {
            return $query->when($data['value'], function (Builder $query, string $status): Builder {
                return match ($status) {
                    'in_stock' => $query->whereHas('product', fn($q) => $q->where('stock_quantity', '>=', 10)),
                    'low_stock' => $query->whereHas('product', fn($q) => $q->whereBetween('stock_quantity', [1, 9])),
                    'out_of_stock' => $query->whereHas('product', fn($q) => $q->where('stock_quantity', 0)),
                };
            });
        }), Tables\Filters\Filter::make('added_at')->label(__('admin.customers.fields.added_at'))->form([Forms\Components\DatePicker::make('added_from')->label(__('admin.customers.filters.added_from')), Forms\Components\DatePicker::make('added_until')->label(__('admin.customers.filters.added_until'))])->query(function (Builder $query, array $data): Builder {
            return $query->when($data['added_from'], fn(Builder $query, $date): Builder => $query->whereDate('added_at', '>=', $date))->when($data['added_until'], fn(Builder $query, $date): Builder => $query->whereDate('added_at', '<=', $date));
        })])->headerActions([Tables\Actions\CreateAction::make()->label(__('admin.customers.add_to_wishlist'))])->actions([Tables\Actions\ViewAction::make()->label(__('admin.actions.view')), Tables\Actions\EditAction::make()->label(__('admin.actions.edit')), Tables\Actions\DeleteAction::make()->label(__('admin.actions.delete'))])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()->label(__('admin.actions.delete_selected'))])])->defaultSort('added_at', 'desc');
    }
}