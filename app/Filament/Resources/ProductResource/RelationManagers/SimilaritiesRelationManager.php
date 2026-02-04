<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SimilaritiesRelationManager extends RelationManager
{
    protected static string $relationship = 'similarities';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('messages.similarities');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('similar_product_id')
                    ->label(__('messages.similar_product'))
                    ->relationship('similarProduct', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Product $record) => "
                        <div class='flex items-center gap-3 py-1'>
                            <div class='flex-shrink-0 w-10 h-10 overflow-hidden rounded-lg bg-gray-100 border border-gray-200'>
                                <img src='{$record->thumbnail}' alt='{$record->name}' class='w-full h-full object-cover' onerror=\"this.src='https://ui-avatars.com/api/?name=" . urlencode($record->name) . "&color=7F9CF5&background=EBF4FF'\" />
                            </div>
                            <div class='flex flex-col min-w-0'>
                                <span class='text-sm font-medium text-gray-900 truncate'>{$record->name}</span>
                                <span class='text-xs text-gray-500 truncate'>{$record->sku}</span>
                            </div>
                        </div>
                    ")
                    ->allowHtml()
                    ->required()
                    ->searchable()
                    ->preload(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\ImageColumn::make('similarProduct.images.path')
                    ->label(__('messages.image'))
                    ->disk('public')
                    ->limit(1)
                    ->square(),
                TextColumn::make('similarProduct.name')
                    ->label(__('messages.similar_product'))
                    ->description(fn ($record) => $record->similarProduct?->sku)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('score')
                    ->label(__('messages.score'))
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('calculated_at')
                    ->label(__('messages.calculated_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
