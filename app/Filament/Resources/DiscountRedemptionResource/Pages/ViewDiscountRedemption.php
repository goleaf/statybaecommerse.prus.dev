<?php declare(strict_types=1);

namespace App\Filament\Resources\DiscountRedemptionResource\Pages;

use App\Filament\Resources\DiscountRedemptionResource;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Filament\Actions;
use Filament\Infolists;

class ViewDiscountRedemption extends ViewRecord
{
    protected static string $resource = DiscountRedemptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Redemption Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('discount.name')
                            ->label('Discount')
                            ->weight(FontWeight::Bold),
                        Infolists\Components\TextEntry::make('code.code')
                            ->label('Code')
                            ->badge()
                            ->color('info'),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('User'),
                        Infolists\Components\TextEntry::make('order.id')
                            ->label('Order')
                            ->badge(),
                    ])
                    ->columns(2),
                Infolists\Components\Section::make('Financial Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('amount_saved')
                            ->label('Amount Saved')
                            ->money('EUR')
                            ->weight(FontWeight::Bold)
                            ->color('success'),
                        Infolists\Components\TextEntry::make('currency_code')
                            ->label('Currency')
                            ->badge(),
                    ])
                    ->columns(2),
                Infolists\Components\Section::make('Status & Timing')
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->colors([
                                'warning' => 'pending',
                                'success' => 'redeemed',
                                'danger' => 'expired',
                                'secondary' => 'cancelled',
                            ]),
                        Infolists\Components\TextEntry::make('redeemed_at')
                            ->label('Redeemed At')
                            ->dateTime(),
                    ])
                    ->columns(2),
                Infolists\Components\Section::make('Additional Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label('Notes')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('ip_address')
                            ->label('IP Address')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('user_agent')
                            ->label('User Agent')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Infolists\Components\Section::make('Audit Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('creator.name')
                            ->label('Created By'),
                        Infolists\Components\TextEntry::make('updater.name')
                            ->label('Updated By'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}

