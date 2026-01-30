<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryResource\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.categories.basic_information'))
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
                            TextEntry::make('parent.name')
                                ->label(__('messages.category')),
                            IconEntry::make('is_active')
                                ->label(__('admin.categories.is_active'))
                                ->boolean(),
                        ]),
                ]),
        ]);
    }
}
