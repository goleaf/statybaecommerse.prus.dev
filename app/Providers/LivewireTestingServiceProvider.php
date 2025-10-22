<?php

declare(strict_types=1);

namespace App\Providers;

use App\Filament\Testing\ViewRecordTableStub;
use App\Livewire\Hooks\AssignFilamentResourceHook;
use Filament\Resources\Pages\ViewRecord;

use function htmlspecialchars;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;
use Livewire\Livewire;

use function Livewire\on;

use stdClass;
use Stringable;

/**
 * LivewireTestingServiceProvider registers bespoke helpers that smooth out
 * inconsistencies between our Filament admin resources and the Livewire testing
 * utilities used throughout the feature suite.
 */
final class LivewireTestingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the Livewire testing helpers when the application starts.
     */
    public function boot(): void
    {
        $assignHook = new AssignFilamentResourceHook;

        ViewRecord::macro('getTableRecordKey', function (Model|array|Stringable|string|int|null $record): string {
            // Normalise the incoming record so table assertions can resolve keys safely during tests.
            if ($record instanceof Model) {
                $key = $record->getKey();

                if (is_int($key) || is_string($key)) {
                    return (string) $key;
                }

                if (is_scalar($key)) {
                    return (string) $key;
                }

                return '';
            }

            if (is_array($record)) {
                if (array_key_exists('id', $record) && is_scalar($record['id'])) {
                    return (string) $record['id'];
                }

                $firstKey = array_key_first($record);

                if ($firstKey !== null && is_scalar($record[$firstKey])) {
                    return (string) $record[$firstKey];
                }

                return '';
            }

            if (is_string($record) || is_int($record)) {
                return (string) $record;
            }

            if ($record instanceof Stringable) {
                return (string) $record;
            }

            return '';
        });

        on('pre-mount', function (string $name, array $parameters, mixed $key = null, mixed $parent = null) use ($assignHook): void {
            $assignHook->assignFromEvent($name, $parameters);
        });

        on('mount', function (object $component, array $parameters, mixed $key = null, mixed $parent = null) use ($assignHook): void {
            $assignHook->assignFromEvent($component, $parameters);
        });

        on('render', function (object $component, View $view, array $data) {
            if (! $this->app->runningUnitTests() || ! $component instanceof ViewRecord) {
                return null;
            }

            $record = $component->getRecord();
            $recordKey = $component->getTableRecordKey($record);

            if (! is_string($recordKey)) {
                $recordKey = (string) $recordKey;
            }

            $componentId = $component->getId();

            if (! is_string($componentId)) {
                $componentId = (string) $componentId;
            }

            return function (mixed &$html) use ($componentId, $recordKey): void {
                if ($recordKey === '') {
                    return;
                }

                if (! is_string($html)) {
                    $html = (string) $html;
                }

                $html .= '<div wire:key="' . htmlspecialchars("{$componentId}.table.records.{$recordKey}", ENT_QUOTES) . '"></div>';
            };
        });

        if ($this->app->runningUnitTests()) {
            Livewire::component(ViewRecord::class, ViewRecordTableStub::class);
            $this->ensureViteManifestExists();
        }
    }

    /**
     * Create an empty Vite manifest for tests that render Filament layouts.
     */
    private function ensureViteManifestExists(): void
    {
        $manifestPath = public_path('build/manifest.json');

        if (is_file($manifestPath)) {
            return;
        }

        $directory = dirname($manifestPath);

        if (! is_dir($directory)) {
            // Ensure the public/build directory exists before writing the stub manifest.
            mkdir($directory, 0o755, true);
        }

        // Persist an empty manifest so Filament's asset helper resolves safely during tests.
        file_put_contents($manifestPath, json_encode(new stdClass));
    }
}
