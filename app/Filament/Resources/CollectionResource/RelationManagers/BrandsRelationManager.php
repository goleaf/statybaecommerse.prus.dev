<?php

declare(strict_types=1);

namespace App\Filament\Resources\CollectionResource\RelationManagers;

use App\Filament\Resources\BrandResource;
use App\Models\Brand;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BrandsRelationManager extends RelationManager
{
    protected static string $relationship = 'brands';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.navigation.brands');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Brand $record): string => BrandResource::getUrl('edit', ['record' => $record]))
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
                    ->url(fn (Brand $record): string => BrandResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
