<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalResource\Pages;

use App\Filament\Resources\LegalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListLegals extends ListRecords
{
    protected static string $resource = LegalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('admin.legal.all_documents'))
                ->icon('heroicon-o-document-text'),

            'published' => Tab::make(__('admin.legal.published'))
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_enabled', true)->whereNotNull('published_at'))
                ->badge(fn () => \App\Models\Legal::where('is_enabled', true)->whereNotNull('published_at')->count()),

            'draft' => Tab::make(__('admin.legal.draft'))
                ->icon('heroicon-o-document')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('published_at'))
                ->badge(fn () => \App\Models\Legal::whereNull('published_at')->count()),

            'disabled' => Tab::make(__('admin.legal.disabled'))
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_enabled', false))
                ->badge(fn () => \App\Models\Legal::where('is_enabled', false)->count()),

            'required' => Tab::make(__('admin.legal.required'))
                ->icon('heroicon-o-exclamation-triangle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_required', true))
                ->badge(fn () => \App\Models\Legal::where('is_required', true)->count()),
        ];
    }
}
