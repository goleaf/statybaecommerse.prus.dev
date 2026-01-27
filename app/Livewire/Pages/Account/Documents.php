<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Account;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.templates.account')]
class Documents extends Component
{
    public array $documents = [];

    public function mount(): void
    {
        $user = auth()->user();
        if ($user) {
            $this->documents = $user
                ->documents()
                ->latest('generated_at')
                ->limit(200)
                ->get(['id', 'title', 'format', 'file_path', 'status', 'generated_at'])
                ->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'title' => $doc->title,
                        'format' => $doc->format,
                        'status' => $doc->status,
                        'generated_at' => optional($doc->generated_at)->toDateTimeString(),
                        'url' => $doc->getFileUrl(),
                    ];
                })
                ->toArray();
        }
    }

    public function render(): View
    {
        return view('livewire.pages.account.documents');
    }
}