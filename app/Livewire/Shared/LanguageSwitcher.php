<?php declare(strict_types=1);

namespace App\Livewire\Shared;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

class LanguageSwitcher extends Component
{
    public array $locales = [];

    public string $current;

    public array $links = [];

    public function mount(): void
    {
        $supported = config('app.supported_locales', ['en']);
        $this->locales = array_values(array_filter(array_map('trim', is_array($supported) ? $supported : explode(',', (string) $supported))));
        $this->current = app()->getLocale();

        $full = url()->full();
        $path = parse_url($full, PHP_URL_PATH) ?? '/';
        $qs = parse_url($full, PHP_URL_QUERY);
        $query = $qs ? '?' . $qs : '';

        $parts = explode('/', ltrim($path, '/'));
        if (isset($parts[0]) && in_array($parts[0], $this->locales, true)) {
            array_shift($parts);
        }
        $rest = trim(implode('/', $parts), '/');

        $this->links = [];
        foreach ($this->locales as $loc) {
            $href = $rest === '' ? url("/$loc") : url("/$loc/$rest");
            $this->links[$loc] = $href . $query;
        }
    }

    public function render(): View
    {
        return view('livewire.shared.language-switcher');
    }
}
