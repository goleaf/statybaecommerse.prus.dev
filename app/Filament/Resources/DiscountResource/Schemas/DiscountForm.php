<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountResource\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DiscountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.discounts.basic_information'))
                ->description(__('admin.discounts.basic_information_description'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('messages.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('code')
                                ->label(__('messages.code'))
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(50),
                        ]),
                    RichEditor::make('description')
                        ->label(__('messages.description'))
                        ->columnSpanFull(),
                    Grid::make(3)
                        ->schema([
                            Select::make('type')
                                ->label(__('messages.type'))
                                ->options([
                                    'percentage' => __('admin.discounts.percentage'),
                                    'fixed'      => __('admin.discounts.fixed_amount'),
                                ])
                                ->required(),
                            TextInput::make('value')
                                ->label(__('messages.value'))
                                ->required()
                                ->numeric()
                                ->minValue(0),
                            Toggle::make('is_active')
                                ->label(__('admin.discounts.is_active'))
                                ->default(true),
                        ]),
                ])
                ->columnSpanFull(),
            Section::make(__('admin.discounts.validity'))
                ->description(__('admin.discounts.validity_description'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            DateTimePicker::make('valid_from')
                                ->label(__('admin.discounts.valid_from'))
                                ->default(now()),
                            DateTimePicker::make('valid_until')
                                ->label(__('admin.discounts.valid_until')),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('usage_limit')
                                ->label(__('admin.discounts.usage_limit'))
                                ->numeric()
                                ->minValue(1),
                            TextInput::make('minimum_amount')
                                ->label(__('admin.discounts.minimum_amount'))
                                ->numeric()
                                ->minValue(0)
                                ->step(0.01),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}
