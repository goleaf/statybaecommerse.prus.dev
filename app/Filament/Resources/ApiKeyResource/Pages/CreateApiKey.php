<?php

declare(strict_types=1);

namespace App\Filament\Resources\ApiKeyResource\Pages;

use App\Filament\Resources\ApiKeyResource;
use App\Filament\Resources\ApiKeyResource\Concerns\HandlesApiKeyCredentials;
use App\Models\ApiKey;
use Filament\Resources\Pages\CreateRecord;

final class CreateApiKey extends CreateRecord
{
    use HandlesApiKeyCredentials;

    protected static string $resource = ApiKeyResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $permissions = $data['permissions'] ?? [];

        if (! is_array($permissions)) {
            $permissions = [];
        }

        $data['permissions'] = array_values(array_filter($permissions));

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record instanceof ApiKey) {
            $this->dispatchCredentialModal([
                'key' => $this->record->key,
                'secret' => $this->record->secret,
            ]);
        }
    }
}
