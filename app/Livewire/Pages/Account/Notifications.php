<?php declare(strict_types=1);

namespace App\Livewire\Pages\Account;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

final class Notifications extends Component
{
    public array $notifications = [];

    public function mount(): void
    {
        $user = auth()->user();
        if ($user && method_exists($user, 'notifications') && Schema::hasTable('notifications')) {
            $this->notifications = $user
                ->notifications()
                ->latest()
                ->limit(100)
                ->get(['id', 'type', 'data', 'read_at', 'created_at'])
                ->map(function ($n) {
                    return [
                        'id' => $n->id,
                        'type' => class_basename($n->type),
                        'data' => $n->data,
                        'read_at' => $n->read_at,
                        'created_at' => $n->created_at,
                    ];
                })
                ->toArray();
        }
    }

    public function render(): View
    {
        return view('livewire.pages.account.notifications');
    }
}
