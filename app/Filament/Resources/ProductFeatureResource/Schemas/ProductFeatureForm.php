<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductFeatureResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductFeatureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.product_features.basic_information'))
                    ->schema([
                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->required(),
                        TextInput::make('feature_type')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('feature_key')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('feature_value')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('weight')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),
            ]);
    }
}
