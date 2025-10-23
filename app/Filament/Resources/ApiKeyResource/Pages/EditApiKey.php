<?php

declare(strict_types=1);

namespace App\Filament\Resources\ApiKeyResource\Pages;

use App\Filament\Resources\ApiKeyResource;
use App\Filament\Resources\ApiKeyResource\Concerns\HandlesApiKeyCredentials;
use Filament\Resources\Pages\EditRecord;

final class EditApiKey extends EditRecord
{
    use HandlesApiKeyCredentials;

    protected static string $resource = ApiKeyResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $permissions = $data['permissions'] ?? [];

        if (! is_array($permissions)) {
            $permissions = [];
        }

        $data['permissions'] = array_values(array_filter($permissions));

        return $data;
    }
}
