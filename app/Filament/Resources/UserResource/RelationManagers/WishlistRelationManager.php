<?php declare(strict_types=1);

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class WishlistRelationManager extends RelationManager
{
    protected static string $relationship = 'wishlist';
    protected static ?string $title = 'admin.sections.wishlist';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                    ->searchable(),
                    ->preload(),
            ]);
    }

    public function table(Table $table): Table
    {
    {
        return $table
            ->recordTitleAttribute('product.name')
            ->columns([
                Tables\Columns\ImageColumn::make('product.images.0.url')
                    ->label(__('admin.fields.image'))
                    ->circular(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('admin.fields.product_name'))
                    ->searchable(),
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.price')
                    ->label(__('admin.fields.price'))
                    ->money('EUR'),
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.stock_quantity')
                    ->label(__('admin.fields.stock'))
                    ->numeric(),
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.fields.added_at'))
                    ->dateTime(),
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('in_stock')
                    ->label(__('admin.filters.in_stock'))
                    ->query(fn($query) => $query->whereHas('product', fn($q) => $q->where('stock_quantity', '>', 0))),
                Tables\Filters\Filter::make('out_of_stock')
                    ->label(__('admin.filters.out_of_stock'))
                    ->query(fn($query) => $query->whereHas('product', fn($q) => $q->where('stock_quantity', '<=', 0))),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}

