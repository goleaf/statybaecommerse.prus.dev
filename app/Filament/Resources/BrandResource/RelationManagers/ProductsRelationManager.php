<?php declare(strict_types=1);

namespace App\Filament\Resources\BrandResource\RelationManagers;

use App\Models\Product;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;

final class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $title = 'Brand Products';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->label(__('admin.products.fields.name'))
                    ->required(),
                    ->maxLength(255),
                Forms\Components\TextInput::make('sku')
                    ->label(__('admin.products.fields.sku'))
                    ->required(),
                    ->maxLength(255),
                    ->unique(Product::class, 'sku', ignoreRecord: true),
                Forms\Components\Textarea::make('description')
                    ->label(__('admin.products.fields.description'))
                    ->rows(3),
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('price')
                    ->label(__('admin.products.fields.price'))
                    ->numeric(),
                    ->prefix('€'),
                    ->required(),
                Forms\Components\TextInput::make('stock_quantity')
                    ->label(__('admin.products.fields.stock_quantity'))
                    ->numeric(),
                    ->default(0),
                Forms\Components\Toggle::make('is_published')
                    ->label(__('admin.products.fields.is_published'))
                    ->default(true),
                Forms\Components\Toggle::make('is_featured')
                    ->label(__('admin.products.fields.is_featured'))
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.products.fields.name'))
                    ->searchable(),
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label(__('admin.products.fields.sku'))
                    ->searchable(),
                    ->sortable(),
                    ->copyable(),
                    ->copyMessage(__('admin.common.copied')),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('admin.products.fields.price'))
                    ->money('EUR'),
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label(__('admin.products.fields.stock_quantity'))
                    ->numeric(),
                    ->sortable(),
                    ->badge(),
                    ->color(fn($state) => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 5 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\IconColumn::make('is_published')
                    ->label(__('admin.products.fields.is_published'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('admin.products.fields.is_featured'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.products.fields.created_at'))
                    ->dateTime(),
                    ->sortable(),
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label(__('admin.products.fields.is_published')),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label(__('admin.products.fields.is_featured')),
                Tables\Filters\Filter::make('low_stock')
                    ->label(__('admin.products.filters.low_stock'))
                    ->query(fn(Builder $query): Builder => $query->where('stock_quantity', '<=', 5)),
                Tables\Filters\Filter::make('out_of_stock')
                    ->label(__('admin.products.filters.out_of_stock'))
                    ->query(fn(Builder $query): Builder => $query->where('stock_quantity', '<=', 0)),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('publish')
                        ->label(__('admin.products.actions.publish'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(fn($records) => $records->each->update(['is_published' => true]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label(__('admin.products.actions.unpublish'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(fn($records) => $records->each->update(['is_published' => false]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort("created_at", "desc");
    }
}
    }
}
