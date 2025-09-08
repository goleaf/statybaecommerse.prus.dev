<?php declare(strict_types=1);

namespace App\Filament\Resources\CustomerManagementResource\Pages;

use App\Filament\Resources\CustomerManagementResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Infolists\Components;

final class ViewCustomerManagement extends ViewRecord
{
    protected static string $resource = CustomerManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return __('Customer Details');
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\Section::make(__('Customer Information'))
                    ->schema([
                        Components\TextEntry::make('name')
                            ->label(__('Name')),
                        Components\TextEntry::make('email')
                            ->label(__('Email'))
                            ->copyable(),
                        Components\TextEntry::make('preferred_locale')
                            ->label(__('Language'))
                            ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                        Components\IconEntry::make('email_verified_at')
                            ->label(__('Email Verified'))
                            ->boolean()
                            ->getStateUsing(fn (User $record): bool => !is_null($record->email_verified_at)),
                    ])
                    ->columns(2),

                Components\Section::make(__('Account Status'))
                    ->schema([
                        Components\IconEntry::make('is_active')
                            ->label(__('Active'))
                            ->boolean(),
                        Components\TextEntry::make('timezone')
                            ->label(__('Timezone')),
                        Components\TextEntry::make('last_login_at')
                            ->label(__('Last Login'))
                            ->dateTime()
                            ->placeholder(__('Never')),
                        Components\TextEntry::make('created_at')
                            ->label(__('Registered'))
                            ->dateTime(),
                    ])
                    ->columns(2),

                Components\Section::make(__('Order Statistics'))
                    ->schema([
                        Components\TextEntry::make('orders_count')
                            ->label(__('Total Orders'))
                            ->numeric(),
                        Components\TextEntry::make('orders_sum_total')
                            ->label(__('Total Spent'))
                            ->money('EUR'),
                        Components\TextEntry::make('avg_order_value')
                            ->label(__('Avg Order Value'))
                            ->getStateUsing(fn (User $record): string => 
                                $record->orders_count > 0 
                                    ? '€' . number_format($record->orders_sum_total / $record->orders_count, 2)
                                    : '€0.00'
                            ),
                        Components\TextEntry::make('last_order_date')
                            ->label(__('Last Order'))
                            ->getStateUsing(fn (User $record): ?string => 
                                $record->orders()->latest()->first()?->created_at?->format('M j, Y')
                            )
                            ->placeholder(__('No orders')),
                    ])
                    ->columns(2),
            ]);
    }
}
