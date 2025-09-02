<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RedemptionController extends Controller
{
    public function index(Request $request): View
    {
        $q = DB::table('sh_discount_redemptions as r')
            ->join('sh_discounts as d', 'd.id', '=', 'r.discount_id')
            ->leftJoin('users as u', 'u.id', '=', 'r.user_id')
            ->select('r.*', 'd.code as discount_code', 'd.type as discount_type', DB::raw('u.email as user_email'))
            ->orderByDesc('r.redeemed_at');

        if ($request->filled('discount_id')) {
            $q->where('r.discount_id', (int) $request->integer('discount_id'));
        }
        if ($request->filled('user_id')) {
            $q->where('r.user_id', (int) $request->integer('user_id'));
        }
        if ($request->filled('from')) {
            $q->where('r.redeemed_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $q->where('r.redeemed_at', '<=', $request->date('to'));
        }

        $redemptions = $q->paginate(50)->appends($request->query());
        $discounts = DB::table('sh_discounts')->select('id', 'code', 'type', 'value')->orderByDesc('id')->limit(200)->get();
        return view('livewire.admin.redemptions.index', compact('redemptions', 'discounts'));
    }

    public function exportCsv(Request $request)
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
        return Storage::download($path);
    }
}
