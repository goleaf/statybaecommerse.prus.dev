<?php

declare(strict_types=1);

namespace App\Livewire\Shared;

use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * LanguageSwitcher
 *
 * Livewire component for LanguageSwitcher with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property array<int, string>    $locales
 * @property string                $current
 * @property array<string, string> $links
 */
class LanguageSwitcher extends Component
{
    /**
     * @var array<int, string>
     */
    public array $locales = [];

    public string $current;

    /**
     * @var array<string, string>
     */
    public array $links = [];

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        $supported = config('app.supported_locales', ['en']);
        $rawLocales = is_array($supported) ? $supported : explode(',', (string) $supported);
        $this->locales = array_values(array_filter(array_map(
            static fn ($locale): string => trim((string) $locale),
            $rawLocales
        ), static fn (string $locale): bool => $locale !== ''));
        $this->current = app()->getLocale();
        $full = url()->full();
        $path = parse_url($full, PHP_URL_PATH);
        $path = is_string($path) ? $path : '/';
        $qs = parse_url($full, PHP_URL_QUERY);
        $query = $qs ? '?' . $qs : '';
        $parts = explode('/', ltrim($path, '/'));
        if ($parts !== [] && $parts[0] !== '' && in_array($parts[0], $this->locales, true)) {
            array_shift($parts);
        }
        $rest = trim(implode('/', $parts), '/');
        $this->links = [];
        foreach ($this->locales as $loc) {
            $href = $rest === '' ? url('/' . $loc) : url('/' . $loc . '/' . $rest);
            $this->links[$loc] = $href . $query;
        }
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.shared.language-switcher');
    }
}
