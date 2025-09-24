<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Legal as LegalModel;
use Livewire\Component;

/**
 * LegalPage
 *
 * Livewire component for LegalPage with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property LegalModel $legal
 */
final class LegalPage extends Component
{
    public LegalModel $legal;

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(string $slug): void
    {
        $this->legal = LegalModel::where('key', $slug)->where('is_enabled', true)->with(['translations'])->firstOrFail();
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        $translation = $this->legal->translations()->where('locale', app()->getLocale())->first();
        if (! $translation) {
            $translation = $this->legal->translations()->first();
        }

        return view('livewire.pages.legal', ['translation' => $translation])->layout('components.layouts.base', ['title' => $translation?->title ?? $this->legal->key]);
    }
}
