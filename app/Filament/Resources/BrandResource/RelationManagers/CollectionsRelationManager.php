<?php

declare(strict_types=1);

namespace App\Filament\Resources\BrandResource\RelationManagers;

use App\Filament\Resources\CollectionResource;
use App\Models\Collection;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CollectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'collections';

    protected static ?string $relatedResource = CollectionResource::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.navigation.collections');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Collection::query()
                ->whereHas('products', fn (Builder $query): Builder => $query->where('products.brand_id', $this->getOwnerRecord()->getKey()))
                ->distinct())
            ->recordUrl(fn (Collection $record): string => CollectionResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('slug')
                    ->label(__('messages.slug'))
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('view')
                    ->url(fn (Collection $record): string => CollectionResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
