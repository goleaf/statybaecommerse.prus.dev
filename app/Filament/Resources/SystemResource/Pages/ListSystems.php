<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemResource\Pages;

use App\Filament\Resources\SystemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListSystems extends ListRecords
{
    protected static string $resource = SystemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Create System Setting')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Settings')
                ->icon('heroicon-o-cog-6-tooth'),

            'general' => Tab::make('General')
                ->icon('heroicon-o-home')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('category', fn (Builder $q) => $q->where('name', 'General'))),

            'security' => Tab::make('Security')
                ->icon('heroicon-o-shield-check')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('category', fn (Builder $q) => $q->where('name', 'Security'))),

            'performance' => Tab::make('Performance')
                ->icon('heroicon-o-bolt')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('category', fn (Builder $q) => $q->where('name', 'Performance'))),

            'ui_ux' => Tab::make('UI/UX')
                ->icon('heroicon-o-paint-brush')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('category', fn (Builder $q) => $q->where('name', 'UI/UX'))),

            'api' => Tab::make('API')
                ->icon('heroicon-o-code-bracket')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('category', fn (Builder $q) => $q->where('name', 'API'))),

            'required' => Tab::make('Required')
                ->icon('heroicon-o-exclamation-triangle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_required', true)),

            'public' => Tab::make('Public')
                ->icon('heroicon-o-globe-alt')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_public', true)),

            'readonly' => Tab::make('Read Only')
                ->icon('heroicon-o-lock-closed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_readonly', true)),
        ];
    }
}
