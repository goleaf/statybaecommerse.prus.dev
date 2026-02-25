<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingCategoryResource\Schemas;

use App\Models\SystemSettingCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SystemSettingCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Textarea::make('description')
                    ->rows(3),
                TextInput::make('icon')
                    ->maxLength(255),
                TextInput::make('color')
                    ->maxLength(255),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Select::make('parent_id')
                    ->options(static fn (): array => SystemSettingCategory::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
