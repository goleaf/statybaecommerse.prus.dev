<?php declare(strict_types=1);

namespace App\Livewire\Admin\Campaigns;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    public array $campaigns = [];

    public function mount(): void
    {
        $this->campaigns = DB::table('sh_discount_campaigns')->orderByDesc('id')->limit(50)->get()->map(fn($c) => (array) $c)->all();
    }

    #[Layout('layouts.templates.app')]
    public function render()
    {
        return view('livewire.admin.campaigns.index');
    }
}
