<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountResource\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DiscountInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.discounts.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('name')
                                ->label(__('messages.name')),
                            TextEntry::make('code')
                                ->label(__('messages.code')),
                        ]),
                    TextEntry::make('description')
                        ->label(__('messages.description'))
                        ->html()
                        ->columnSpanFull(),
                    Grid::make(3)
                        ->schema([
                            TextEntry::make('type')
                                ->label(__('messages.type'))
                                ->formatStateUsing(fn ($state) => match ($state) {
                                    'percentage' => __('admin.discounts.percentage'),
                                    'fixed'      => __('admin.discounts.fixed_amount'),
                                    default      => $state,
                                }),
                            TextEntry::make('value')
                                ->label(__('messages.value'))
                                ->formatStateUsing(fn ($state, $record) => $record->type === 'percentage'
                                    ? $state . '%'
                                    : '€' . number_format((float)$state, 2)
                                ),
                            IconEntry::make('is_active')
                                ->label(__('admin.discounts.is_active'))
                                ->boolean(),
                        ]),
                ]),
            Section::make(__('admin.discounts.validity'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('valid_from')
                                ->label(__('admin.discounts.valid_from'))
                                ->dateTime(),
                            TextEntry::make('valid_until')
                                ->label(__('admin.discounts.valid_until'))
                                ->dateTime()
                                ->placeholder(__('admin.discounts.no_expiry')),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('usage_limit')
                                ->label(__('admin.discounts.usage_limit')),
                            TextEntry::make('minimum_amount')
                                ->label(__('admin.discounts.minimum_amount'))
                                ->money('EUR'),
                        ]),
                ])
                ->collapsible(),
        ]);
    }
}
