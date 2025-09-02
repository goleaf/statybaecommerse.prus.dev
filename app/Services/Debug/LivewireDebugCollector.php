<?php declare(strict_types=1);

namespace App\Services\Debug;

use Illuminate\Support\Facades\Log;

class LivewireDebugCollector
{
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
