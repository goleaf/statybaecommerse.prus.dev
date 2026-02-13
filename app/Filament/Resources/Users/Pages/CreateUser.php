<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * @var array<int, int>
     */
    private array $customerGroupIds = [];

    /**
     * @var array<int, int>
     */
    private array $partnerIds = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->customerGroupIds = $this->normalizeIds($data['customer_group_ids'] ?? []);
        $this->partnerIds = $this->normalizeIds($data['partner_ids'] ?? []);

        unset($data['customer_group_ids'], $data['partner_ids']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        if (! $record instanceof User) {
            return;
        }

        $this->syncRelations($record);
    }

    /**
     * @param  array<int, mixed> $ids
     * @return array<int, int>
     */
    private function normalizeIds(array $ids): array
    {
        $normalized = array_map(static fn (mixed $id): int => (int) $id, $ids);

        return array_values(array_unique(array_filter($normalized, static fn (int $id): bool => $id > 0)));
    }

    private function syncRelations(User $user): void
    {
        $now = now();

        $customerGroupPayload = [];
        foreach ($this->customerGroupIds as $customerGroupId) {
            $customerGroupPayload[$customerGroupId] = [
                'assigned_at' => $now,
            ];
        }
        $user->customerGroups()->sync($customerGroupPayload);

        $user->partners()->sync($this->partnerIds);
    }
}
