<?php declare(strict_types=1);

namespace App\Filament\Resources\EnumManagementResource\Pages;

use App\Filament\Resources\EnumManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;

class ViewEnumManagement extends ViewRecord
{
    protected static string $resource = EnumManagementResource::class;

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
                Infolists\Components\Section::make('Enum Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('enum_type')
                            ->label('Enum Type')
                            ->badge()
                            ->color('info'),
                        Infolists\Components\TextEntry::make('value')
                            ->label('Value')
                            ->weight(FontWeight::Bold),
                        Infolists\Components\TextEntry::make('label')
                            ->label('Label'),
                        Infolists\Components\TextEntry::make('color')
                            ->label('Color')
                            ->badge()
                            ->color(fn (string $state): string => $state),
                        Infolists\Components\TextEntry::make('icon')
                            ->label('Icon')
                            ->icon(fn (string $state): string => $state)
                            ->toggleable(),
                        Infolists\Components\TextEntry::make('symbol')
                            ->label('Symbol')
                            ->toggleable(),
                        Infolists\Components\TextEntry::make('decimal_places')
                            ->label('Decimal Places')
                            ->toggleable(),
                    ])
                    ->columns(2),
            ]);
    }
}
