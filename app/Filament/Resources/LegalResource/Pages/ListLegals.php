<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalResource\Pages;

use App\Filament\Resources\LegalResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListLegals extends ListRecords
{
    protected static string $resource = LegalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('legal.actions.create'))
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('legal.tabs.all'))
                ->icon('heroicon-o-document-text'),

            'enabled' => Tab::make(__('legal.tabs.enabled'))
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_enabled', true)),

            'disabled' => Tab::make(__('legal.tabs.disabled'))
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_enabled', false)),

            'required' => Tab::make(__('legal.tabs.required'))
                ->icon('heroicon-o-exclamation-triangle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_required', true)),

            'published' => Tab::make(__('legal.tabs.published'))
                ->icon('heroicon-o-eye')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('published_at')),

            'draft' => Tab::make(__('legal.tabs.draft'))
                ->icon('heroicon-o-pencil')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('published_at')),
        ];
    }
}
