<?php

declare(strict_types=1);

namespace App\Filament\Resources\AuditTrailResource\Pages;

use App\Filament\Resources\AuditTrailResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

final class ViewAuditTrail extends ViewRecord
{
    protected static string $resource = AuditTrailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label(__('admin.audit_trails.back_to_list'))
                ->icon('heroicon-o-arrow-uturn-left')
                ->url(fn (): string => AuditTrailResource::getUrl())
                ->color('gray'),
        ];
    }
}
