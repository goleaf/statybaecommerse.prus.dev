<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DiscountCodeSeeder extends Seeder
{
    public function run(): void
    {
        // For each existing discount, generate a small batch of codes
        $columns = DB::getSchemaBuilder()->getColumnListing('discounts');
        $select = ['id'];
        if (in_array('name', $columns, true)) {
            $select[] = 'name';
        } elseif (in_array('code', $columns, true)) {
            $select[] = 'code as name';
        }
        $discounts = DB::table('discounts')->get($select);
        foreach ($discounts as $discount) {
            // Generate 20 codes per discount, if not already present
            for ($i = 0; $i < 20; $i++) {
                $base = is_string($discount->name ?? null) ? $discount->name : 'DISCOUNT';
                $prefix = Str::upper(Str::slug($base));
                $code = $prefix.'-'.Str::upper(Str::random(6));
                $exists = DB::table('discount_codes')->where('code', $code)->exists();
                if ($exists) {
                    continue;
                }
                DB::table('discount_codes')->insert([
                    'discount_id' => $discount->id,
                    'code' => $code,
                    'expires_at' => now()->addMonths(6),
                    'max_uses' => 100,
                    'usage_count' => 0,
                    'metadata' => json_encode([]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
