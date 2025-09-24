<?php

declare(strict_types=1);

namespace App\Services\Debug;

use Illuminate\Support\Facades\Log;

/**
 * TranslationDebugCollector
 *
 * Service class containing TranslationDebugCollector business logic, external integrations, and complex operations with proper error handling and logging.
 */
class TranslationDebugCollector
{
    /**
     * Handle logTranslationQuery functionality with proper error handling.
     */
    public function logTranslationQuery(string $key, string $locale, string $value, bool $fromCache): void
    {
        $data = compact('key', 'locale', 'value', 'fromCache');
        if (function_exists('debugbar') && app()->bound('debugbar')) {
            try {
                app('debugbar')->addMessage($data, 'translation');
            } catch (\Throwable $e) {
                // ignore
            }
        }
        Log::debug('Translation query', $data);
    }
}
