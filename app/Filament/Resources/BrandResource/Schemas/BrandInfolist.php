<?php

declare(strict_types=1);

namespace App\Filament\Resources\BrandResource\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BrandInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.brands.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('name')
                                ->label(__('messages.name')),
                            TextEntry::make('slug')
                                ->label(__('messages.slug')),
                        ]),
                    TextEntry::make('description')
                        ->label(__('messages.description'))
                        ->html()
                        ->columnSpanFull(),
                    Grid::make(2)
                        ->schema([
                            IconEntry::make('is_active')
                                ->label(__('admin.brands.is_active'))
                                ->boolean(),
                            IconEntry::make('is_premium')
                                ->label(__('admin.brands.is_premium'))
                                ->boolean(),
                        ]),
                ]),
            Section::make(__('messages.media'))
                ->schema([
                    SpatieMediaLibraryImageEntry::make('logo')
                        ->label(__('messages.image'))
                        ->collection('logo')
                        ->circular(),
                ]),
            Section::make(__('admin.brands.social_links'))
                ->schema([
                    TextEntry::make('social_links')
                        ->label(__('admin.brands.social_links'))
                        ->listWithLineBreaks()
                        ->formatStateUsing(fn ($state) => "{$state['platform']}: {$state['url']}"),
                ]),
        ]);
    }
}
