<?php

declare(strict_types=1);

namespace App\Observers;

use App\Services\TranslationHookService;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class TranslationObserver
{
    /**
     * Cache translation key column lookups per table to avoid repeated schema hits.
     *
     * @var array<string, bool>
     */
    private static array $translationKeyColumnCache = [];

    public function __construct(
        private readonly TranslationHookService $translationService
    ) {}

    /**
     * Handle model saving event
     */
    public function saving(Model $model): void
    {
        $this->processTranslatableFields($model);
    }

    /**
     * Handle model created event
     */
    public function created(Model $model): void
    {
        $this->logTranslationActivity($model, 'created');
    }

    /**
     * Handle model updated event
     */
    public function updated(Model $model): void
    {
        $this->logTranslationActivity($model, 'updated');
    }

    private function processTranslatableFields(Model $model): void
    {
        $translatableFields = $this->getTranslatableFields($model);

        if (empty($translatableFields)) {
            return;
        }

        foreach ($translatableFields as $field) {
            if ($model->isDirty($field) && ! empty($model->$field)) {
                $this->createTranslationEntry($model, $field);
            }
        }
    }

    private function createTranslationEntry(Model $model, string $field): void
    {
        try {
            $value = $model->$field;
            $modelName = strtolower(class_basename($model));
            $key = $this->translationService->generateTranslationKey($value, $modelName);

            // Create translation for all supported locales
            $translations = [
                config('app.locale', 'lt') => $value,
            ];

            $this->translationService->addTranslation($key, $translations);

            // Store translation key reference if model supports it
            if ($this->canStoreTranslationKey($model, $field)) {
                $model->{$field . '_translation_key'} = $key;
            }

        } catch (Exception $e) {
            Log::error('Failed to create translation entry', [
                'model' => get_class($model),
                'field' => $field,
                'value' => $model->$field ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function getTranslatableFields(Model $model): array
    {
        // Check if model has getTranslatableFields method (from HasTranslations trait)
        if (method_exists($model, 'getTranslatableFields')) {
            return $model->getTranslatableFields();
        }

        // Check if model has translatable fields property defined
        if (property_exists($model, 'translatableFields') && is_array($model->translatableFields)) {
            return array_intersect($model->translatableFields, $model->getFillable());
        }

        // Default translatable fields for common models
        $defaultFields = [
            'name', 'title', 'description', 'content', 'summary',
            'meta_title', 'meta_description', 'alt_text', 'caption',
        ];

        // Return only fields that exist in the model's fillable array
        return array_intersect($defaultFields, $model->getFillable());
    }

    private function logTranslationActivity(Model $model, string $action): void
    {
        $translatableFields = $this->getTranslatableFields($model);

        if (! empty($translatableFields)) {
            Log::info('Translation hook processed', [
                'model'               => get_class($model),
                'action'              => $action,
                'id'                  => $model->getKey(),
                'translatable_fields' => $translatableFields,
            ]);
        }
    }

    private function canStoreTranslationKey(Model $model, string $field): bool
    {
        $column = $field . '_translation_key';
        $cacheKey = $model->getTable() . '.' . $column;

        if (array_key_exists($cacheKey, self::$translationKeyColumnCache)) {
            return self::$translationKeyColumnCache[$cacheKey];
        }

        try {
            $exists = Schema::hasColumn($model->getTable(), $column);
        } catch (Throwable $e) {
            $exists = false;
        }

        self::$translationKeyColumnCache[$cacheKey] = $exists;

        return $exists;
    }
}
