<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerGroups\Pages;

use App\Filament\Resources\CustomerGroups\CustomerGroupResource;
use App\Models\CustomerGroup;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerGroup extends CreateRecord
{
    protected static string $resource = CustomerGroupResource::class;

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

        if ($userId <= 0 || ! $this->record instanceof CustomerGroup) {
            return;
        }

        $this->record->users()->syncWithoutDetaching([
            $userId => [
                'assigned_at' => now(),
            ],
        ]);
    }

    protected function getRedirectUrl(): string
    {
        if (is_string($this->redirectUrl) && $this->redirectUrl !== '') {
            return $this->redirectUrl;
        }

        return parent::getRedirectUrl();
    }
}
