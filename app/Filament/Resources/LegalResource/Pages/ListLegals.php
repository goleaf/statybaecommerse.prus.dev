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
                ->label('Create Legal Document')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Documents')
                ->icon('heroicon-o-document-text'),

            'enabled' => Tab::make('Enabled')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_enabled', true)),

            'disabled' => Tab::make('Disabled')
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_enabled', false)),

            'required' => Tab::make('Required')
                ->icon('heroicon-o-exclamation-triangle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_required', true)),

            'published' => Tab::make('Published')
                ->icon('heroicon-o-eye')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('published_at')),

            'draft' => Tab::make('Draft')
                ->icon('heroicon-o-pencil')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('published_at')),
        ];
    }
}
