<?php

declare(strict_types=1);

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('messages.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('messages.name'))
                                    ->required(),
                                TextInput::make('price')
                                    ->label(__('messages.price'))
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('€'),
                                Toggle::make('is_active')
                                    ->label(__('messages.is_active'))
                                    ->required(),
                            ]),
                        Textarea::make('description')
                            ->label(__('messages.description'))
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->extraAttributes([
                        'class' => 'bg-slate-50/90 rounded-2xl p-6',
                    ]),
            ]);
    }
}
