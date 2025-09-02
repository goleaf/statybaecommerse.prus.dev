<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderStatusController extends Controller
{
    public function edit(string $number): ViewContract
    {
        /** @var Order $order */
        $order = Order::query()->where('number', $number)->firstOrFail();
        return view('livewire.pages.admin.orders.status', compact('order'));
    }

    public function update(Request $request, string $number): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['nullable', 'string', 'max:50'],
            'payment_status' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        /** @var Order $order */
        $order = Order::query()->where('number', $number)->firstOrFail();

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

            $order->save();
        });

        return back()->with('status', __('Order updated'));
    }

    public function updateTracking(Request $request, string $number): RedirectResponse
    {
        $data = $request->validate([
            'tracking_number' => ['nullable', 'string', 'max:255'],
            'tracking_url' => ['nullable', 'url', 'max:2048'],
        ]);

        /** @var Order $order */
        $order = Order::query()->where('number', $number)->firstOrFail();

        DB::transaction(function () use ($order, $data): void {
            $row = DB::table('order_shippings')
                ->where('order_id', $order->id)
                ->orderByDesc('id')
                ->first();

            if ($row) {
                DB::table('order_shippings')
                    ->where('id', $row->id)
                    ->update([
                        'tracking_number' => $data['tracking_number'] ?? null,
                        'tracking_url' => $data['tracking_url'] ?? null,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('order_shippings')->insert([
                    'order_id' => $order->id,
                    'tracking_number' => $data['tracking_number'] ?? null,
                    'tracking_url' => $data['tracking_url'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // append to timeline
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

        return back()->with('status', __('Tracking updated'));
    }
}
