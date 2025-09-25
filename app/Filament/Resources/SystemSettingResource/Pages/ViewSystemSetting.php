<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingResource\Pages;

use App\Filament\Resources\SystemSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewSystemSetting extends ViewRecord
{
    protected static string $resource = SystemSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $view = parent::render();
        $record = $this->getRecord();

        return $view->with([
            '__record_id' => $record->getKey(),
            '__record_key' => $record->key ?? null,
        ]);
    }
}
