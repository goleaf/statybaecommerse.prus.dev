<?php

declare(strict_types=1);

namespace App\Filament\Resources\Partners\Pages;

use App\Filament\Resources\Partners\PartnerResource;
use App\Models\Partner;
use Filament\Resources\Pages\CreateRecord;

class CreatePartner extends CreateRecord
{
    protected static string $resource = PartnerResource::class;

    public ?int $attachUserId = null;

    public ?string $redirectUrl = null;

    public function mount(): void
    {
        parent::mount();

        $this->attachUserId = request()->integer('attach_user_id') ?: null;

        $redirectUrl = request()->query('redirect');
        $this->redirectUrl = is_string($redirectUrl) && $redirectUrl !== ''
            ? $redirectUrl
            : null;
    }

    protected function afterCreate(): void
    {
        $userId = $this->attachUserId ?? 0;

        if ($userId <= 0 || ! $this->record instanceof Partner) {
            return;
        }

        $this->record->users()->syncWithoutDetaching([$userId]);
    }

    protected function getRedirectUrl(): string
    {
        if (is_string($this->redirectUrl) && $this->redirectUrl !== '') {
            return $this->redirectUrl;
        }

        return parent::getRedirectUrl();
    }
}
