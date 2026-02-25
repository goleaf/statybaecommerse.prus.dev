<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['first_name'] = $this->normalizeLocalizedText($data['first_name'] ?? null);
        $data['last_name'] = $this->normalizeLocalizedText($data['last_name'] ?? null);

        return $data;
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    private function normalizeLocalizedText(mixed $state): ?string
    {
        if ($state === null) {
            return null;
        }

        if (is_object($state)) {
            $state = (array) $state;
        }

        if (is_array($state)) {
            return $this->resolveLocalizedValue($state);
        }

        if (! is_string($state)) {
            return (string) $state;
        }

        $trimmed = trim($state);
        if ($trimmed === '') {
            return '';
        }

        if (! str_starts_with($trimmed, '{') && ! str_starts_with($trimmed, '[')) {
            return $state;
        }

        $decoded = json_decode($trimmed, true);

        if (! is_array($decoded)) {
            return $state;
        }

        return $this->resolveLocalizedValue($decoded) ?? $state;
    }

    /**
     * @param array<string|int, mixed> $values
     */
    private function resolveLocalizedValue(array $values): ?string
    {
        $locale = (string) app()->getLocale();
        $fallbackLocale = (string) config('app.fallback_locale', 'en');

        foreach ([$locale, $fallbackLocale, 'lt', 'en'] as $candidateLocale) {
            $candidate = $values[$candidateLocale] ?? null;

            if (is_scalar($candidate)) {
                $candidateText = trim((string) $candidate);

                if ($candidateText !== '') {
                    return $candidateText;
                }
            }
        }

        foreach ($values as $value) {
            if (is_scalar($value)) {
                $valueText = trim((string) $value);

                if ($valueText !== '') {
                    return $valueText;
                }
            }
        }

        return null;
    }
}
