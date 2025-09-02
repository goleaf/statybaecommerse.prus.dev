<?php declare(strict_types=1);

namespace App\Livewire\Admin\Redemptions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url]
    public ?int $discount_id = null;

    #[Url]
    public ?int $user_id = null;

    #[Url]
    public ?string $from = null;

    #[Url]
    public ?string $to = null;

    public array $discounts = [];

    public array $rows = [];

    public function mount(): void
    {
        $this->discounts = DB::table('sh_discounts')->select('id', 'code', 'type', 'value')->orderByDesc('id')->limit(200)->get()->map(fn($d) => (array) $d)->all();
        $this->load();
    }

    public function load(): void
    {
        $q = DB::table('sh_discount_redemptions as r')
            ->join('sh_discounts as d', 'd.id', '=', 'r.discount_id')
            ->leftJoin('users as u', 'u.id', '=', 'r.user_id')
            ->select('r.*', 'd.code as discount_code', 'd.type as discount_type', DB::raw('u.email as user_email'))
            ->orderByDesc('r.redeemed_at');

        if ($this->discount_id) {
            $q->where('r.discount_id', (int) $this->discount_id);
        }
        if ($this->user_id) {
            $q->where('r.user_id', (int) $this->user_id);
        }
        if (!empty($this->from)) {
            $q->where('r.redeemed_at', '>=', $this->from);
        }
        if (!empty($this->to)) {
            $q->where('r.redeemed_at', '<=', $this->to);
        }

        $this->rows = $q->limit(200)->get()->map(fn($r) => (array) $r)->all();
    }

    public function exportCsv(): void
    {
        $filename = 'discount_redemptions_' . now()->format('Ymd_Hi') . '.csv';
        $path = 'exports/' . $filename;
        $stream = fopen('php://temp', 'w+');
        fputcsv($stream, ['discount_id', 'code', 'user_id', 'email', 'order_id', 'amount_saved', 'currency', 'redeemed_at']);

        $q = DB::table('sh_discount_redemptions as r')
            ->join('sh_discounts as d', 'd.id', '=', 'r.discount_id')
            ->leftJoin('users as u', 'u.id', '=', 'r.user_id')
            ->select('r.*', 'd.code as discount_code', DB::raw('u.email as user_email'))
            ->orderByDesc('r.redeemed_at');
        foreach ($q->limit(5000)->cursor() as $row) {
            fputcsv($stream, [$row->discount_id, $row->discount_code, $row->user_id, $row->user_email, $row->order_id, $row->amount_saved, $row->currency_code, $row->redeemed_at]);
        }
        rewind($stream);
        Storage::put($path, stream_get_contents($stream));
        fclose($stream);

        $this->dispatch('notify', status: 'success', message: __('CSV exported to storage/app/:path', ['path' => $path]));
    }

    #[Layout('layouts.templates.app')]
    public function render()
    {
        return view('livewire.admin.redemptions.index');
    }
}
