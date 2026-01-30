<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductFeatureResource\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductFeatureInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.product_features.basic_information'))
                    ->schema([
                        TextEntry::make('product.name')
                            ->label(__('messages.product')),
                        TextEntry::make('feature_type'),
                        TextEntry::make('feature_key'),
                        TextEntry::make('feature_value'),
                        TextEntry::make('weight'),
                        IconEntry::make('is_active')
                            ->boolean(),
                    ])->columns(2),
            ]);
    }
}
