<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderController extends Controller
{
    public function show(string $number): View
    {
        try {
            $order = Order::query()->where('number', $number)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            abort(302, '', [
                'Location' => route('home', ['locale' => app()->getLocale()]),
            ]);
        }

        // Load applied discounts from redemptions
        $redemptions = \DB::table('sh_discount_redemptions')
            ->join('sh_discounts', 'sh_discounts.id', '=', 'sh_discount_redemptions.discount_id')
            ->where('order_id', $order->id)
            ->select('sh_discounts.type', 'sh_discounts.code', 'sh_discount_redemptions.amount_saved')
            ->get();

        return view('livewire.pages.order.confirmed', compact('order', 'redemptions'));
    }
}
