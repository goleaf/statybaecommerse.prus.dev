<?php

declare (strict_types=1);
namespace App\Services\Debug;

use Illuminate\Support\Facades\Log;
/**
 * LivewireDebugCollector
 * 
 * Service class containing LivewireDebugCollector business logic, external integrations, and complex operations with proper error handling and logging.
 * 
 */
class LivewireDebugCollector
{
    /**
     * Handle logComponentLifecycle functionality with proper error handling.
     * @param string $component
     * @param string $phase
     * @param array $data
     * @return void
     */
    public function logComponentLifecycle(string $component, string $phase, array $data = []): void
    {
        $payload = compact('component', 'phase', 'data');
        if (function_exists('debugbar') && app()->bound('debugbar')) {
            try {
                app('debugbar')->addMessage($payload, 'livewire');
            } catch (\Throwable $e) {
                // ignore
            }
        }
        Log::debug('Livewire lifecycle', $payload);
    }
}