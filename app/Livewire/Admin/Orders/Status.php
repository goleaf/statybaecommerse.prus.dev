<?php declare(strict_types=1);

namespace App\Livewire\Admin\Orders;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use App\Models\Order;

class Status extends Component
{
    public string $number;

    public ?Order $order = null;

    #[Url]
    public ?string $status = null;

    #[Url]
    public ?string $payment_status = null;

    public ?string $note = null;

    public ?string $tracking_number = null;

    public ?string $tracking_url = null;

    public function mount(string $number): void
    {
        $this->number = $number;
        $this->order = Order::query()->where('number', $number)->firstOrFail();
        $this->status = $this->order->status;
        $this->payment_status = $this->order->payment_status;
    }

    public function updateStatus(): void
    {
        $data = $this->validate([
            'status' => ['nullable', 'string', 'max:50'],
            'payment_status' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = Order::query()->where('number', $this->number)->firstOrFail();
        DB::transaction(function () use ($order, $data): void {
            $changed = false;
            if (!empty($data['status']) && $data['status'] !== $order->status) {
                $order->status = $data['status'];
                $changed = true;
            }
            if (!empty($data['payment_status']) && $data['payment_status'] !== $order->payment_status) {
                $order->payment_status = $data['payment_status'];
                $changed = true;
            }

            $timeline = (array) json_decode((string) ($order->timeline ?? '[]'), true);
            $timeline[] = [
                'at' => now()->toIso8601String(),
                'by' => optional(Auth::user())->id,
                'status' => $data['status'] ?? null,
                'payment_status' => $data['payment_status'] ?? null,
                'note' => $data['note'] ?? null,
            ];
            $order->timeline = $timeline;

            if ($changed) {
                $order->save();
            }
        });

        $this->dispatch('notify', status: 'success', message: __('Order updated'));
        $this->order->refresh();
    }

    public function updateTracking(): void
    {
        $data = $this->validate([
            'tracking_number' => ['nullable', 'string', 'max:255'],
            'tracking_url' => ['nullable', 'url', 'max:2048'],
        ]);

        $order = Order::query()->where('number', $this->number)->firstOrFail();
        DB::transaction(function () use ($order, $data): void {
            $row = DB::table('sh_order_shippings')
                ->where('order_id', $order->id)
                ->orderByDesc('id')
                ->first();

            if ($row) {
                DB::table('sh_order_shippings')
                    ->where('id', $row->id)
                    ->update([
                        'tracking_number' => $data['tracking_number'] ?? null,
                        'tracking_url' => $data['tracking_url'] ?? null,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('sh_order_shippings')->insert([
                    'order_id' => $order->id,
                    'tracking_number' => $data['tracking_number'] ?? null,
                    'tracking_url' => $data['tracking_url'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $timeline = (array) json_decode((string) ($order->timeline ?? '[]'), true);
            $timeline[] = [
                'at' => now()->toIso8601String(),
                'by' => optional(Auth::user())->id,
                'tracking_number' => $data['tracking_number'] ?? null,
                'tracking_url' => $data['tracking_url'] ?? null,
            ];
            $order->timeline = $timeline;
            $order->save();
        });

        $this->dispatch('notify', status: 'success', message: __('Tracking updated'));
        $this->order->refresh();
    }

    #[Layout('layouts.templates.app')]
    public function render()
    {
        return view('livewire.pages.admin.orders.status');
    }
}
