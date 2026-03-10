<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Account;

use App\Models\OrderInvoice;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.templates.account')]
#[Title('Profile')]
final class Profile extends Component
{
    public function render(): View
    {
        $userId = auth()->id();

        $recentInvoices = OrderInvoice::query()
            ->with(['order', 'file'])
            ->where('is_current', true)
            ->whereHas('order', static function (Builder $query) use ($userId): void {
                $query->withoutGlobalScopes()->where('user_id', $userId);
            })
            ->latest('generated_at')
            ->limit(8)
            ->get();

        return view('livewire.pages.account.profile', [
            'recentInvoices' => $recentInvoices,
        ]);
    }
}
