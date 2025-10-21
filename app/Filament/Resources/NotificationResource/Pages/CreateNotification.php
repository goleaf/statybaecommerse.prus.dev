<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateNotification extends CreateRecord
{
    protected static string $resource = NotificationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->syncReadState($data);
    }

    /**
     * Ensure read state and timestamp remain in sync.
     */
    private function syncReadState(array $data): array
    {
        $isRead = (bool) ($data['is_read'] ?? false);

        if ($isRead) {
            $data['read_at'] = $data['read_at'] ?? now();
        } else {
            $data['read_at'] = null;
            $data['is_read'] = false;
        }

        return $data;
    }
}
