<?php declare(strict_types=1);

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use Filament\Resources\Pages\ViewRecord\Concerns\Translatable;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

final class ViewCampaign extends ViewRecord
{
    use Translatable;

    protected static string $resource = CampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_list')
                ->label(__('common.back_to_list'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('index'))
                ->tooltip(__('common.back_to_list_tooltip')),
            Actions\LocaleSwitcher::make(),
            Actions\EditAction::make(),
        ];
    }
}
