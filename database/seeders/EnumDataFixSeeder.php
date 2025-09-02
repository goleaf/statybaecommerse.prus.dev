<?php declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EnumDataFixSeeder extends Seeder
{
    public function run(): void
    {
        $this->fixDiscountEnums();
        $this->fixCollectionEnums();
        $this->fixProductEnums();
        $this->fixOrderEnums();
        $this->fixOrderRefundEnums();
    }

    protected function fixDiscountEnums(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('sh_discounts')) {
            return;
        }

        $typeMap = [
            'fixed' => 'fixed_amount',
            'fixed-amount' => 'fixed_amount',
            'fixed amount' => 'fixed_amount',
            'fixed_amount' => 'fixed_amount',
            'percentage' => 'percentage',
            'percent' => 'percentage',
        ];
        $applyMap = [
            'order' => 'order',
            'entire_order' => 'order',
            'entire-order' => 'order',
            'products' => 'products',
            'product' => 'products',
            'items' => 'products',
        ];
        $eligibilityMap = [
            'everyone' => 'everyone',
            'all' => 'everyone',
            'customers' => 'customers',
            'customer' => 'customers',
        ];

        $rows = DB::table('sh_discounts')->select('id', 'type', 'apply_to', 'eligibility')->get();
        foreach ($rows as $row) {
            $updates = [];
            // type
            if (is_string($row->type)) {
                $key = strtolower(trim($row->type));
                $mapped = $typeMap[$key] ?? null;
                if ($mapped === null) {
                    // default to percentage if unknown
                    $mapped = 'percentage';
                }
                if ($mapped !== $row->type) {
                    $updates['type'] = $mapped;
                }
            } else {
                $updates['type'] = 'percentage';
            }
            // apply_to
            if (property_exists($row, 'apply_to')) {
                $key = is_string($row->apply_to) ? strtolower(trim($row->apply_to)) : '';
                $mapped = $applyMap[$key] ?? 'order';
                if ($mapped !== $row->apply_to) {
                    $updates['apply_to'] = $mapped;
                }
            }
            // eligibility
            if (property_exists($row, 'eligibility')) {
                $key = is_string($row->eligibility) ? strtolower(trim($row->eligibility)) : '';
                $mapped = $eligibilityMap[$key] ?? 'everyone';
                if ($mapped !== $row->eligibility) {
                    $updates['eligibility'] = $mapped;
                }
            }

            if (!empty($updates)) {
                DB::table('sh_discounts')->where('id', $row->id)->update($updates);
            }
        }
    }

    protected function fixCollectionEnums(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('sh_collections')) {
            return;
        }
        $typeMap = [
            'manual' => 'manual',
            'auto' => 'auto',
            'automatic' => 'auto',
        ];
        $rows = DB::table('sh_collections')->select('id', 'type')->get();
        foreach ($rows as $row) {
            $key = is_string($row->type) ? strtolower(trim($row->type)) : '';
            $mapped = $typeMap[$key] ?? 'manual';
            if ($mapped !== $row->type) {
                DB::table('sh_collections')->where('id', $row->id)->update(['type' => $mapped]);
            }
        }
    }

    protected function fixProductEnums(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('sh_products')) {
            return;
        }
        $typeMap = [
            'standard' => 'standard',
            'variant' => 'standard',
            'external' => 'standard',
            'virtual' => 'standard',
            'simple' => 'standard',
        ];
        $rows = DB::table('sh_products')->select('id', 'type')->get();
        foreach ($rows as $row) {
            $key = is_string($row->type) ? strtolower(trim($row->type)) : '';
            $mapped = $typeMap[$key] ?? 'standard';
            if ($mapped !== $row->type) {
                DB::table('sh_products')->where('id', $row->id)->update(['type' => $mapped]);
            }
        }
    }

    protected function fixOrderEnums(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('sh_orders')) {
            return;
        }
        $statusMap = [
            'new' => 'new',
            'shipped' => 'shipped',
            'delivered' => 'delivered',
            'pending' => 'pending',
            'paid' => 'paid',
            'registered' => 'registered',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
        ];
        $rows = DB::table('sh_orders')->select('id', 'status')->get();
        foreach ($rows as $row) {
            $key = is_string($row->status) ? strtolower(trim($row->status)) : '';
            $mapped = $statusMap[$key] ?? 'pending';
            if ($mapped !== $row->status) {
                DB::table('sh_orders')->where('id', $row->id)->update(['status' => $mapped]);
            }
        }
    }

    protected function fixOrderRefundEnums(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('sh_order_refunds')) {
            return;
        }
        $statusMap = [
            'awaiting' => 'awaiting',
            'pending' => 'pending',
            'treatment' => 'treatment',
            'partial_refund' => 'partial_refund',
            'refunded' => 'refunded',
            'rejected' => 'rejected',
            'cancelled' => 'cancelled',
        ];
        $rows = DB::table('sh_order_refunds')->select('id', 'status')->get();
        foreach ($rows as $row) {
            $key = is_string($row->status) ? strtolower(trim($row->status)) : '';
            $mapped = $statusMap[$key] ?? 'pending';
            if ($mapped !== $row->status) {
                DB::table('sh_order_refunds')->where('id', $row->id)->update(['status' => $mapped]);
            }
        }
    }
}
