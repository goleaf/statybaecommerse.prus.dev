<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerGroups\Pages;

use App\Filament\Resources\CustomerGroups\CustomerGroupResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\Users\RelationManagers\CustomerGroupsRelationManager;
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
        $this->redirectUrl = $this->resolveSafeRedirectUrl($redirectUrl);
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

        $ownerRelationUrl = $this->resolveOwnerRelationUrl();

        if ($ownerRelationUrl !== null) {
            return $ownerRelationUrl;
        }

        return parent::getRedirectUrl();
    }

    private function resolveSafeRedirectUrl(mixed $redirectUrl): ?string
    {
        if (! is_string($redirectUrl) || trim($redirectUrl) === '') {
            return null;
        }

        $redirectHost = parse_url($redirectUrl, PHP_URL_HOST);
        $requestHost = request()->getHost();

        if (is_string($redirectHost) && $redirectHost !== '' && strcasecmp($redirectHost, $requestHost) !== 0) {
            return null;
        }

        $path = parse_url($redirectUrl, PHP_URL_PATH);

        if (! is_string($path) || $path === '' || str_ends_with($path, '/livewire/update')) {
            return null;
        }

        return $redirectUrl;
    }

    private function resolveOwnerRelationUrl(): ?string
    {
        $userId = $this->attachUserId ?? 0;

        if ($userId <= 0) {
            return null;
        }

        $parameters = [
            'record' => $userId,
        ];

        $relationTabKey = array_search(CustomerGroupsRelationManager::class, UserResource::getRelations(), true);

        if ($relationTabKey !== false) {
            $parameters['relation'] = (string) $relationTabKey;
        }

        return UserResource::getUrl('view', $parameters);
    }
}
