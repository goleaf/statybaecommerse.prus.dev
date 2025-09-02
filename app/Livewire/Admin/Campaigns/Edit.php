<?php declare(strict_types=1);

namespace App\Livewire\Admin\Campaigns;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

class Edit extends Component
{
    public ?int $id = null;

    public string $name = '';

    public string $slug = '';

    public ?string $starts_at = null;

    public ?string $ends_at = null;

    public ?int $zone_id = null;

    public ?int $channel_id = null;

    public string $status = 'active';

    public array $discount_ids = [];

    public array $zones = [];

    public array $channels = [];

    public array $discounts = [];

    public function mount(?int $id = null): void
    {
        $this->id = $id;
        $this->zones = DB::table('sh_zones')->pluck('name', 'id')->all();
        $this->channels = DB::table('sh_channels')->pluck('name', 'id')->all();
        $this->discounts = DB::table('sh_discounts')->select('id', 'type', 'value', 'code', 'status')->orderByDesc('id')->limit(500)->get()->map(fn($d) => (array) $d)->all();

        if ($id) {
            $campaign = DB::table('sh_discount_campaigns')->where('id', $id)->first();
            abort_unless($campaign, 404);
            $this->name = (string) $campaign->name;
            $this->slug = (string) $campaign->slug;
            $this->starts_at = $campaign->starts_at ? (string) $campaign->starts_at : null;
            $this->ends_at = $campaign->ends_at ? (string) $campaign->ends_at : null;
            $this->zone_id = $campaign->zone_id ? (int) $campaign->zone_id : null;
            $this->channel_id = $campaign->channel_id ? (int) $campaign->channel_id : null;
            $this->status = (string) $campaign->status;
            $this->discount_ids = DB::table('sh_campaign_discount')->where('campaign_id', $id)->pluck('discount_id')->all();
        }
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'zone_id' => ['nullable', 'integer'],
            'channel_id' => ['nullable', 'integer'],
            'status' => ['required', 'string'],
            'discount_ids' => ['nullable', 'array'],
        ]);

        if ($this->id) {
            $id = $this->id;
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
        } else {
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
            $this->id = (int) $id;
        }

        $this->dispatch('notify', status: 'success', message: __('Campaign saved'));
    }

    #[Layout('layouts.templates.app')]
    public function render()
    {
        return view('livewire.admin.campaigns.edit');
    }
}
