<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsCategories\Schemas;

use App\Models\NewsCategory;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class NewsCategoryForm
{
    public static function configure(Schema $form): Schema
    {
        return $schema
            ->schema([
                Toggle::make('is_visible')
                    ->label('Is Visible')
                    ->default(true)
                    ->required(),
                Select::make('parent_id')
                    ->label('Parent Category')
                    ->options(static function (?NewsCategory $record): array {
                        return NewsCategory::query()
                            ->withoutGlobalScopes()
                            ->when(
                                $record,
                                static fn ($query) => $query->whereKeyNot($record->getKey()),
                            )
                            ->orderBy('sort_order')
                            ->pluck('name', 'id')
                            ->all();
                    })
                    ->searchable()
                    ->preload()
                    ->nullable(),
                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0)
                    ->required(),
                ColorPicker::make('color')
                    ->label('Color')
                    ->nullable(),
                TextInput::make('icon')
                    ->label('Icon')
                    ->placeholder('heroicon-o-rectangle-stack')
                    ->nullable(),
            ]);
    }
}
