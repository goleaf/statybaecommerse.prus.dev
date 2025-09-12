<?php declare(strict_types=1);

namespace App\Livewire\Pages\Account;

use Illuminate\Contracts\View\View;
use Livewire\Component;

final class Documents extends Component
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
                        'file_path' => $doc->file_path,
                        'status' => $doc->status,
                        'generated_at' => $doc->generated_at,
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
