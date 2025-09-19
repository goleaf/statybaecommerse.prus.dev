<?php declare(strict_types=1);

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('admin.documents.tabs.all'))
                ->icon('heroicon-o-document-text'),
            'draft' => Tab::make(__('admin.documents.tabs.draft'))
                ->icon('heroicon-o-pencil-square')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft')),
            'generated' => Tab::make(__('admin.documents.tabs.generated'))
                ->icon('heroicon-o-cog-6-tooth')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'generated')),
            'published' => Tab::make(__('admin.documents.tabs.published'))
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'published')),
        ];
    }
}
