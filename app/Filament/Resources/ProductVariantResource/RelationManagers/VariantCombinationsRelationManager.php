<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class VariantCombinationsRelationManager extends RelationManager
{
    protected static string $relationship = 'variantCombinations'; // Need to define in model.

    protected static ?string $recordTitleAttribute = 'combination_hash';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.variant_combinations.plural_model_label');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('formatted_combinations')
                    ->label(__('admin.variant_combinations.attribute_combinations')),
                IconColumn::make('is_available')
                    ->sortable()
                    ->label(__('admin.variant_combinations.is_available'))
                    ->boolean(),
            ])
            ->filters([
                //
            ]);
    }
}
