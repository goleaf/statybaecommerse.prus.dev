<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\VariantCombination;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class VariantCombinationsRelationManager extends RelationManager
{
    protected static string $relationship = 'variantCombinations'; // wait, need to define this in Product model

    protected static ?string $recordTitleAttribute = 'combination_hash';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.variant_combinations.plural_model_label');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                KeyValue::make('attribute_combinations')
                    ->label(__('admin.variant_combinations.attribute_combinations'))
                    ->required(),
                Toggle::make('is_available')
                    ->label(__('admin.variant_combinations.is_available'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('formatted_combinations')
                    ->label(__('admin.variant_combinations.attribute_combinations'))
                    ->sortable(),
                IconColumn::make('is_available')
                    ->label(__('admin.variant_combinations.is_available'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('messages.created_at'))
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
            ]);
    }
}
