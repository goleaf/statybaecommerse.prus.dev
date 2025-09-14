<?php

declare(strict_types=1);

namespace App\Filament\Resources\SeoDataResource\Pages;

use App\Filament\Resources\SeoDataResource;
use App\Filament\Resources\SeoDataResource\Widgets;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final /**
 * ViewSeoData
 * 
 * Filament resource for admin panel management.
 */
class ViewSeoData extends ViewRecord
{
    protected static string $resource = SeoDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_list')
                ->label(__('common.back_to_list'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('index'))
                ->tooltip(__('common.back_to_list_tooltip')),
            Actions\EditAction::make()
                ->label(__('admin.seo_data.edit')),
            Actions\DeleteAction::make()
                ->label(__('admin.seo_data.delete')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            Widgets\SeoOptimizationWidget::class,
        ];
    }
}
