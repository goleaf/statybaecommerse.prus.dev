<?php

namespace App\Filament\Resources\RecommendationAnalytics\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RecommendationAnalyticsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('block_id')
                    ->relationship('block', 'name'),
                Select::make('config_id')
                    ->relationship('config', 'name'),
                Select::make('user_id')
                    ->relationship('user', 'name'),
                Select::make('product_id')
                    ->relationship('product', 'name'),
                TextInput::make('action')
                    ->required(),
                TextInput::make('ctr')
                    ->numeric(),
                TextInput::make('conversion_rate')
                    ->numeric(),
                Textarea::make('metrics')
                    ->columnSpanFull(),
                DatePicker::make('date')
                    ->required(),
            ]);
    }
}
