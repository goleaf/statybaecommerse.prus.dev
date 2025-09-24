<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsCategories\Schemas;

use App\Models\NewsCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class NewsCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('is_visible')
                    ->label('Is Visible')
                    ->default(true)
                    ->required(),
                Select::make('parent_id')
                    ->label('Parent Category')
                    ->options(NewsCategory::all()->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0)
                    ->required(),
                TextInput::make('color')
                    ->label('Color')
                    ->placeholder('#000000')
                    ->nullable(),
                TextInput::make('icon')
                    ->label('Icon')
                    ->placeholder('heroicon-o-rectangle-stack')
                    ->nullable(),
            ]);
    }
}
