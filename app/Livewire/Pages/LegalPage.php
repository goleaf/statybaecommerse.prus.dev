<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Legal as LegalModel;
use Livewire\Component;

final /**
 * LegalPage
 * 
 * Livewire component for reactive frontend functionality.
 */
class LegalPage extends Component
{
    public LegalModel $legal;

    public function mount(string $slug): void
    {
        $this->legal = LegalModel::where('key', $slug)
            ->where('is_enabled', true)
            ->with(['translations'])
            ->firstOrFail();
    }

    public function render()
    {
        $translation = $this->legal->translations()
            ->where('locale', app()->getLocale())
            ->first();

        if (! $translation) {
            $translation = $this->legal->translations()->first();
        }

        return view('livewire.pages.legal', [
            'translation' => $translation,
        ])->layout('components.layouts.base', [
            'title' => $translation?->title ?? $this->legal->key,
        ]);
    }
}
