<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminPresetDiscountsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1) VIP 12% sitewide (stacking off -> single_best), priority 20, customer_group condition
            $vipGroupId = DB::table('customer_groups')->where('code', 'vip')->value('id');
            if ($vipGroupId) {
                $vipId = $this->upsertDiscount([
                    'name' => 'VIP 12% Off',
                    'type' => 'percentage',
                    'value' => 12.0,
                    'priority' => 20,
                    'exclusive' => false,
                    'stacking_policy' => 'single_best',
                ]);
                $this->ensureCondition($vipId, 'customer_group', [$vipGroupId]);
            }

            // 2) Partner tiers: Gold 20%, Silver 12%, with partner_tier condition; generate codes (GOLD-*, SILVER-*)
            $goldId = $this->upsertDiscount([
                'name' => 'Partner Gold 20%',
                'type' => 'percentage',
                'value' => 20.0,
                'priority' => 30,
                'exclusive' => false,
                'stacking_policy' => 'stack',
            ]);
            $this->ensureCondition($goldId, 'partner_tier', ['gold']);
            $this->generateCodes($goldId, 'GOLD', 500);

            $silverId = $this->upsertDiscount([
                'name' => 'Partner Silver 12%',
                'type' => 'percentage',
                'value' => 12.0,
                'priority' => 40,
                'exclusive' => false,
                'stacking_policy' => 'stack',
            ]);
            $this->ensureCondition($silverId, 'partner_tier', ['silver']);

            // 3) Category weekend sale: Shoes 15% off, Sat–Sun (6,7), EU zone only, stacking single_best
            $shoesCategoryId = DB::table('categories')->whereRaw('LOWER(slug) = ?', ['shoes'])->value('id')
                ?? DB::table('categories')->whereRaw('LOWER(name) = ?', ['shoes'])->value('id');
            $euZoneId = DB::table('zones')->whereRaw('LOWER(code) = ?', ['lt'])->value('id')
                ?? DB::table('zones')->whereRaw('LOWER(name) LIKE ?', ['%lithuania%'])->value('id');
            if ($shoesCategoryId && $euZoneId) {
                $shoesId = $this->upsertDiscount([
                    'name' => 'Weekend Shoes 15%',
                    'type' => 'percentage',
                    'value' => 15.0,
                    'priority' => 50,
                    'exclusive' => false,
                    'stacking_policy' => 'single_best',
                    'weekday_mask' => '6,7',
                ]);
                $this->ensureCondition($shoesId, 'category', [$shoesCategoryId]);
                $this->ensureCondition($shoesId, 'zone', [$euZoneId]);
            }

            // 4) BOGO T-Shirts collection: B2G1 cheapest free
            $teesCollectionId = DB::table('collections')->whereRaw('LOWER(slug) = ?', ['t-shirts'])->value('id')
                ?? DB::table('collections')->whereRaw('LOWER(name) = ?', ['t-shirts'])->value('id');
            if ($teesCollectionId) {
                $bogoId = $this->upsertDiscount([
                    'name' => 'BOGO T-Shirts B2G1',
                    'type' => 'bogo',
                    'value' => 0.0,
                    'priority' => 60,
                    'exclusive' => false,
                    'stacking_policy' => 'stack',
                    'metadata' => json_encode(['buy_qty' => 2, 'get_qty' => 1, 'percent_off' => 100]),
                ]);
                $this->ensureCondition($bogoId, 'collection', [$teesCollectionId]);
            }

            // 5) Free shipping over €99 (EUR only), applies via free_shipping + cart_total threshold
            $freeShipId = $this->upsertDiscount([
                'name' => 'Free Shipping Over 99 EUR',
                'type' => 'fixed',
                'value' => 0.0,
                'priority' => 70,
                'exclusive' => false,
                'free_shipping' => true,
                'currency_restrictions' => json_encode(['EUR']),
            ]);
            $this->ensureCondition($freeShipId, 'cart_total', 99.0, 'greater_than');

            // 6) Student group code STUDENT15, per_customer_limit=3, weekday Mon–Fri after 18:00
            $studentGroupId = DB::table('customer_groups')->where('code', 'student')->value('id');
            $studentId = $this->upsertDiscount([
                'name' => 'Student 15% (Evenings)',
                'type' => 'percentage',
                'value' => 15.0,
                'priority' => 80,
                'exclusive' => false,
                'per_customer_limit' => 3,
                'weekday_mask' => '1,2,3,4,5',
                'time_window' => json_encode(['start' => '18:00', 'end' => '23:59', 'tz' => 'UTC']),
            ]);
            if ($studentGroupId) {
                $this->ensureCondition($studentId, 'customer_group', [$studentGroupId]);
            }
            $this->ensureCode($studentId, 'STUDENT15');

            // 7) First-order only: €10 off
            $firstOrderId = $this->upsertDiscount([
                'name' => 'First Order €10',
                'type' => 'fixed',
                'value' => 10.0,
                'priority' => 90,
                'exclusive' => false,
                'first_order_only' => true,
                'stacking_policy' => 'single_best',
            ]);

            // 8) USD-only: $5 off
            $usdId = $this->upsertDiscount([
                'name' => 'USD $5 Off',
                'type' => 'fixed',
                'value' => 5.0,
                'priority' => 100,
                'exclusive' => false,
                'currency_restrictions' => json_encode(['EUR']),
            ]);
        });
    }

    private function upsertDiscount(array $data): int
    {
        // Shopper core requires a unique code; prefer provided code or derive from name
        $code = $data['code'] ?? Str::upper(Str::slug($data['name'] ?? Str::random(6)));
        $existing = DB::table('discounts')->where('code', $code)->first();
        $columns = $this->getColumns('discounts');
        $base = [
            'code' => $code,
            'type' => $data['type'] ?? 'percentage',
            'value' => (int) round($data['value'] ?? 0),
            'apply_to' => $data['apply_to'] ?? 'entire_order',
            'min_required' => $data['min_required'] ?? 'none',
            'min_required_value' => $data['min_required_value'] ?? null,
            'eligibility' => $data['eligibility'] ?? 'everyone',
            'is_active' => $data['is_active'] ?? 1,
            'start_at' => now(),
            'end_at' => null,
        ];
        // Optional extended columns if present
        foreach ([
            'priority', 'exclusive', 'applies_to_shipping', 'free_shipping', 'first_order_only',
            'per_customer_limit', 'per_code_limit', 'per_day_limit', 'channel_restrictions',
            'currency_restrictions', 'weekday_mask', 'time_window', 'stacking_policy',
        ] as $opt) {
            if (in_array($opt, $columns, true) && array_key_exists($opt, $data)) {
                $base[$opt] = $data[$opt];
            }
        }
        // Some schemas may have a name column; if so, store a human label
        if (in_array('name', $columns, true) && isset($data['name'])) {
            $base['name'] = $data['name'];
        }
        if (in_array('status', $columns, true) && ! isset($base['status'])) {
            $base['status'] = 'active';
        }
        if ($existing) {
            DB::table('discounts')->where('id', $existing->id)->update(array_merge($base, [
                'updated_at' => now(),
            ]));

            return (int) $existing->id;
        }

        return (int) DB::table('discounts')->insertGetId(array_merge($base, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    private function ensureCondition(int $discountId, string $type, $value, string $operator = 'greater_than'): void
    {
        $encoded = is_array($value) ? json_encode($value) : (is_numeric($value) ? json_encode($value) : (string) $value);
        $exists = DB::table('discount_conditions')
            ->where('discount_id', $discountId)
            ->where('type', $type)
            ->where('value', $encoded)
            ->exists();
        if (! $exists) {
            DB::table('discount_conditions')->insert([
                'discount_id' => $discountId,
                'type' => $type,
                'operator' => in_array($type, ['cart_total', 'item_qty']) ? $operator : 'equals_to',
                'value' => $encoded,
                'position' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function getColumns(string $table): array
    {
        try {
            return DB::getSchemaBuilder()->getColumnListing($table);
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function generateCodes(int $discountId, string $prefix, int $quantity): void
    {
        for ($i = 0; $i < $quantity; $i++) {
            $code = strtoupper($prefix).'-'.Str::upper(Str::random(8));
            $exists = DB::table('discount_codes')->where('code', $code)->exists();
            if ($exists) {
                continue;
            }
            DB::table('discount_codes')->insert([
                'discount_id' => $discountId,
                'code' => $code,
                'expires_at' => now()->addYear(),
                'max_uses' => 1000,
                'usage_count' => 0,
                'metadata' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function ensureCode(int $discountId, string $code): void
    {
        $exists = DB::table('discount_codes')->where('code', $code)->exists();
        if (! $exists) {
            DB::table('discount_codes')->insert([
                'discount_id' => $discountId,
                'code' => $code,
                'expires_at' => now()->addYear(),
                'max_uses' => 10000,
                'usage_count' => 0,
                'metadata' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
