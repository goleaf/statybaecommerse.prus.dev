<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceResource\RelationManagers;

use App\Models\Product;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'product';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('messages.product');
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['primaryImage']))
            ->columns([
                ImageColumn::make('main_image')
                    ->label(__('messages.image'))
                    ->disk('public')
                    ->getStateUsing(static fn (Product $record): ?string => $record->primaryImage?->path)
                    ->circular(),
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('sku')
                    ->label(__('messages.sku'))
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                \Filament\Tables\Actions\Action::make('attach_product')
                    ->label(__('admin.actions.associate'))
                    ->form([
                        \Filament\Forms\Components\Select::make('product_id')
                            ->label(__('messages.product'))
                            ->options(Product::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (array $data, Model $ownerRecord) {
                        $ownerRecord->update([
                            'priceable_id'   => $data['product_id'],
                            'priceable_type' => Product::class,
                        ]);
                    }),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('detach')
                    ->label(__('admin.actions.dissociate'))
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->action(function (Model $ownerRecord) {
                        $ownerRecord->update([
                            'priceable_id'   => null,
                            'priceable_type' => null,
                        ]);
                    }),
            ]);
    }
}
