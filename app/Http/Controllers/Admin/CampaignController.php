<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CampaignController extends Controller
{
    public function index(): View
    {
        $this->applyPreferredLocale();
        $campaigns = DB::table('sh_discount_campaigns')->orderByDesc('id')->paginate(20);
        return view('livewire.admin.campaigns.index', compact('campaigns'));
    }

    public function create(): View
    {
        $this->applyPreferredLocale();
        $zones = DB::table('sh_zones')->pluck('name', 'id');
        $channels = DB::table('sh_channels')->pluck('name', 'id');
        return view('livewire.admin.campaigns.edit', ['campaign' => null, 'zones' => $zones, 'channels' => $channels, 'attached' => collect(), 'discounts' => $this->discounts()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->applyPreferredLocale();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'zone_id' => ['nullable', 'integer'],
            'channel_id' => ['nullable', 'integer'],
            'status' => ['required', 'string'],
            'discount_ids' => ['nullable', 'array'],
        ]);

        $id = DB::transaction(function () use ($data) {
            $campaignId = DB::table('sh_discount_campaigns')->insertGetId([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
                'zone_id' => $data['zone_id'] ?? null,
                'channel_id' => $data['channel_id'] ?? null,
                'status' => $data['status'],
                'metadata' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            if (!empty($data['discount_ids'])) {
                $rows = collect($data['discount_ids'])->map(fn($d) => ['campaign_id' => $campaignId, 'discount_id' => (int) $d])->all();
                DB::table('sh_campaign_discount')->insert($rows);
            }
            return $campaignId;
        });

        return redirect()->route('admin.campaigns.index')->with('status', __('Campaign created: :id', ['id' => $id]));
    }

    public function edit(int $id): View
    {
        $this->applyPreferredLocale();
        $campaign = DB::table('sh_discount_campaigns')->where('id', $id)->first();
        abort_unless($campaign, 404);
        $zones = DB::table('sh_zones')->pluck('name', 'id');
        $channels = DB::table('sh_channels')->pluck('name', 'id');
        $attached = DB::table('sh_campaign_discount')->where('campaign_id', $id)->pluck('discount_id');
        return view('livewire.admin.campaigns.edit', ['campaign' => $campaign, 'zones' => $zones, 'channels' => $channels, 'attached' => $attached, 'discounts' => $this->discounts()]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->applyPreferredLocale();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'zone_id' => ['nullable', 'integer'],
            'channel_id' => ['nullable', 'integer'],
            'status' => ['required', 'string'],
            'discount_ids' => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($data, $id) {
            DB::table('sh_discount_campaigns')->where('id', $id)->update([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
                'zone_id' => $data['zone_id'] ?? null,
                'channel_id' => $data['channel_id'] ?? null,
                'status' => $data['status'],
                'updated_at' => now(),
            ]);
            DB::table('sh_campaign_discount')->where('campaign_id', $id)->delete();
            if (!empty($data['discount_ids'])) {
                $rows = collect($data['discount_ids'])->map(fn($d) => ['campaign_id' => $id, 'discount_id' => (int) $d])->all();
                DB::table('sh_campaign_discount')->insert($rows);
            }
        });

        return redirect()->route('admin.campaigns.index')->with('status', __('Campaign updated.'));
    }

    protected function discounts()
    {
        return DB::table('sh_discounts')->select('id', 'type', 'value', 'code', 'status')->orderByDesc('id')->limit(500)->get();
    }

    protected function applyPreferredLocale(): void
    {
        $preferred = request()->user()?->preferred_locale ?: request('locale');
        if (is_string($preferred) && $preferred !== '' && in_array($preferred, explode(',', (string) config('app.supported_locales', 'en')), true)) {
            \Illuminate\Support\Facades\App::setLocale($preferred);
        }
    }
}
