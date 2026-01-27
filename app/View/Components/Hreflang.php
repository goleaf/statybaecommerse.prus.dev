<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Support\Locales;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\Component;

final class Hreflang extends Component
{
    /**
     * @var array<string, string>
     */
    public array $alternateLocales = [];

    public function __construct(?array $alternateLocales = null)
    {
        $provided = $alternateLocales ?? ViewFacade::shared('alternateLocales');

        if (is_array($provided) && $provided !== []) {
            $this->alternateLocales = $this->withXDefault($provided);

            return;
        }

        $this->alternateLocales = $this->buildFromCurrentUrl();
    }

    public function render(): View
    {
        return view('components.hreflang');
    }

    /**
     * @param  array<string, string> $provided
     * @return array<string, string>
     */
    private function withXDefault(array $provided): array
    {
        if (array_key_exists('x-default', $provided)) {
            return $provided;
        }

        $first = reset($provided);
        if ($first) {
            $provided['x-default'] = $first;
        }

        return $provided;
    }

    /**
     * @return array<string, string>
     */
    private function buildFromCurrentUrl(): array
    {
        $locales = Locales::supported();
        $full = url()->full();
        $path = parse_url($full, PHP_URL_PATH) ?? '/';
        $qs = parse_url($full, PHP_URL_QUERY);
        $query = $qs ? '?' . $qs : '';

        $parts = explode('/', ltrim($path, '/'));
        if (isset($parts[0]) && in_array($parts[0], $locales, true)) {
            array_shift($parts);
        }
        $rest = trim(implode('/', $parts), '/');

        $alternates = [];
        foreach ($locales as $loc) {
            $href = $rest === '' ? url("/$loc") : url("/$loc/$rest");
            $alternates[$loc] = $href . $query;
        }

        $alternates['x-default'] = url('/' . $rest) . $query;

        return $alternates;
    }
}
