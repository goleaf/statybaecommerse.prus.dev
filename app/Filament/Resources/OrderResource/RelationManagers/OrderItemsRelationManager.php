<?php declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\OrderItem;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
final class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Order Items';
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label(__('admin.order_items.fields.product'))
                    ->relationship('product', 'name')
                    ->searchable(),
                    ->preload(),
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->label(__('admin.order_items.fields.quantity'))
                    ->numeric(),
                    ->required(),
                    ->minValue(1),
                Forms\Components\TextInput::make('price')
                    ->label(__('admin.order_items.fields.price'))
                    ->prefix('€'),
                Forms\Components\TextInput::make('total')
                    ->label(__('admin.order_items.fields.total'))
                    ->prefix('€'),
                    ->disabled(),
                Forms\Components\Textarea::make('notes')
                    ->label(__('admin.order_items.fields.notes'))
                    ->rows(3),
                    ->columnSpanFull(),
            ]);
    }
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.name')
            ->columns([
                Tables\Columns\ImageColumn::make('product.image')
                    ->label(__('admin.order_items.fields.image'))
                    ->getStateUsing(fn(OrderItem $record) => $record->product?->getFirstMediaUrl('images', 'thumb'))
                    ->defaultImageUrl(asset('images/placeholder-product.png'))
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('product.name')
                    ->sortable(),
                    ->weight('medium')
                    ->wrap(),
                Tables\Columns\TextColumn::make('product.sku')
                    ->label(__('admin.order_items.fields.sku'))
                    ->copyable(),
                    ->copyMessage(__('admin.common.copied')),
                    ->weight('mono'),
                Tables\Columns\TextColumn::make('quantity')
                    ->badge(),
                    ->color('primary'),
                Tables\Columns\TextColumn::make('price')
                    ->money('EUR'),
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->color('success'),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(50),
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.order_items.fields.created_at'))
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product')
                    ->preload(),
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ->defaultSort("created_at", "desc");
    }
}
}
