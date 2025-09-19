<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('users.tabs.all'))
                ->icon('heroicon-o-users'),

            'active' => Tab::make(__('users.tabs.active'))
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true)),

            'inactive' => Tab::make(__('users.tabs.inactive'))
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false)),

            'verified' => Tab::make(__('users.tabs.verified'))
                ->icon('heroicon-o-shield-check')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('email_verified_at')),

            'unverified' => Tab::make(__('users.tabs.unverified'))
                ->icon('heroicon-o-shield-exclamation')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('email_verified_at')),
        ];
    }
}
