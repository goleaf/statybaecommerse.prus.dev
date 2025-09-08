<?php declare(strict_types=1);

namespace App\Filament\Resources\DiscountCodeResource\Pages;

use App\Filament\Resources\DiscountCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListDiscountCodes extends ListRecords
{
    protected static string $resource = DiscountCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('bulk_generate')
                ->label(__('Bulk Generate'))
                ->icon('heroicon-o-plus-circle')
                ->form([
                    \Filament\Forms\Components\Select::make('discount_id')
                        ->label(__('Discount'))
                        ->relationship('discount', 'name')
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('count')
                        ->label(__('Number of Codes'))
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->maxValue(1000)
                        ->default(10),
                    \Filament\Forms\Components\TextInput::make('prefix')
                        ->label(__('Prefix'))
                        ->maxLength(10)
                        ->helperText(__('Optional prefix for generated codes')),
                    \Filament\Forms\Components\TextInput::make('length')
                        ->label(__('Code Length'))
                        ->numeric()
                        ->default(8)
                        ->minValue(4)
                        ->maxValue(20),
                ])
                ->action(function (array $data) {
                    for ($i = 0; $i < $data['count']; $i++) {
                        $code = ($data['prefix'] ?? '') . strtoupper(\Illuminate\Support\Str::random($data['length']));
                        \App\Models\DiscountCode::create([
                            'discount_id' => $data['discount_id'],
                            'code' => $code,
                            'status' => 'active',
                        ]);
                    }
                    
                    \Filament\Notifications\Notification::make()
                        ->title(__('Codes Generated'))
                        ->body(__(':count discount codes have been generated successfully.', ['count' => $data['count']]))
                        ->success()
                        ->send();
                }),
        ];
    }
}
