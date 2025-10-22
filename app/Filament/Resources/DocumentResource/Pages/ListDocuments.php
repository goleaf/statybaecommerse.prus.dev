<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\DocumentResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;

class ListDocuments extends BaseListRecords
{
    use HasWidgetTabs;

    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getWidgetTabs(): array
    {
        return [
            'all' => WidgetTab::make(__('admin.documents.tabs.all'))
                ->icon('heroicon-o-document-text')
                ->value(fn () => $this->getResource()::getEloquentQuery()->count()),
            'draft' => WidgetTab::make(__('admin.documents.tabs.draft'))
                ->icon('heroicon-o-pencil-square')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft')),
            'generated' => WidgetTab::make(__('admin.documents.tabs.generated'))
                ->icon('heroicon-o-cog-6-tooth')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'generated')),
            'published' => WidgetTab::make(__('admin.documents.tabs.published'))
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'published')),
        ];
    }
}
