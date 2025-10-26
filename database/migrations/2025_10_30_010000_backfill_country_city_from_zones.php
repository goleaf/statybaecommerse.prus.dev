<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Translate historical zone assignments into the new country and city relations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('zones') || ! Schema::hasTable('countries')) {
            return;
        }

        $issues = [];
        $zoneCountryMap = [];

        // Build a zone to country lookup leveraging multiple ISO code formats for resiliency.
        $zones = DB::table('zones')->select('id', 'code')->get();

        foreach ($zones as $zone) {
            $code = (string) $zone->code;
            $country = DB::table('countries')
                ->where('code', $code)
                ->orWhere('cca2', $code)
                ->orWhere('cca3', $code)
                ->orWhere('iso_code', $code)
                ->first();

            if ($country === null) {
                $issues[] = $this->issueRow('zones', (int) $zone->id, $code, 'Unable to resolve country for zone code');

                continue;
            }

            $zoneCountryMap[(int) $zone->id] = (int) $country->id;
        }

        if ($zoneCountryMap === []) {
            // Nothing to backfill but persist the issues we captured for operators to review.
            $this->storeIssues($issues);

            return;
        }

        // Helper closure to update a table while logging unmapped rows.
        $backfill = function (string $table, string $column) use ($zoneCountryMap, &$issues): void {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column) || ! Schema::hasColumn($table, 'zone_id')) {
                return;
            }

            foreach ($zoneCountryMap as $zoneId => $countryId) {
                $affected = DB::table($table)
                    ->where('zone_id', $zoneId)
                    ->whereNull($column)
                    ->update([$column => $countryId]);

                if ($affected === 0) {
                    continue;
                }
            }

            // Identify remaining rows that still lack the country reference even though a zone was present.
            $dangling = DB::table($table)
                ->select('id', 'zone_id')
                ->whereNotNull('zone_id')
                ->whereNull($column)
                ->get();

            foreach ($dangling as $row) {
                $issues[] = $this->issueRow($table, (int) $row->id, (string) $row->zone_id, sprintf('Failed to backfill %s from zone', $column));
            }
        };

        // Core tables that previously referenced zones directly.
        $backfill('shipping_options', 'country_id');
        $backfill('orders', 'country_id');
        $backfill('price_lists', 'country_id');
        $backfill('discounts', 'country_id');
        $backfill('discount_campaigns', 'country_id');
        $backfill('regions', 'country_id');

        // Cities inherit the country from their zone when missing.
        if (Schema::hasTable('cities') && Schema::hasColumn('cities', 'country_id') && Schema::hasColumn('cities', 'zone_id')) {
            $cities = DB::table('cities')->select('id', 'zone_id')->whereNull('country_id')->whereNotNull('zone_id')->get();

            foreach ($cities as $city) {
                $zoneId = (int) $city->zone_id;
                if (! array_key_exists($zoneId, $zoneCountryMap)) {
                    $issues[] = $this->issueRow('cities', (int) $city->id, (string) $city->zone_id, 'Zone has no country mapping');

                    continue;
                }

                DB::table('cities')->where('id', $city->id)->update(['country_id' => $zoneCountryMap[$zoneId]]);
            }
        }

        // Addresses derive their country from city first, then zone fallback.
        if (Schema::hasTable('addresses') && Schema::hasColumn('addresses', 'country_id')) {
            $addresses = DB::table('addresses')->select('id', 'city_id', 'zone_id', 'country_id')->get();

            foreach ($addresses as $address) {
                if ($address->country_id !== null) {
                    continue;
                }

                $resolvedCountry = null;

                if ($address->city_id !== null) {
                    $resolvedCountry = DB::table('cities')->where('id', $address->city_id)->value('country_id');
                }

                if ($resolvedCountry === null && $address->zone_id !== null) {
                    $zoneId = (int) $address->zone_id;
                    $resolvedCountry = $zoneCountryMap[$zoneId] ?? null;
                }

                if ($resolvedCountry === null) {
                    $issues[] = $this->issueRow('addresses', (int) $address->id, $address->zone_id !== null ? (string) $address->zone_id : null, 'Could not determine country for address');

                    continue;
                }

                DB::table('addresses')->where('id', $address->id)->update(['country_id' => $resolvedCountry]);
            }
        }

        $this->storeIssues($issues);
    }

    /**
     * Revert the backfill by clearing the newly populated columns.
     */
    public function down(): void
    {
        $targets = [
            ['shipping_options', 'country_id'],
            ['orders', 'country_id'],
            ['price_lists', 'country_id'],
            ['discounts', 'country_id'],
            ['discount_campaigns', 'country_id'],
            ['regions', 'country_id'],
        ];

        foreach ($targets as [$table, $column]) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                DB::table($table)->update([$column => null]);
            }
        }

        if (Schema::hasTable('cities') && Schema::hasColumn('cities', 'country_id')) {
            DB::table('cities')->update(['country_id' => null]);
        }

        if (Schema::hasTable('addresses') && Schema::hasColumn('addresses', 'country_id')) {
            DB::table('addresses')->update(['country_id' => null]);
        }

        if (Schema::hasTable('zone_migration_issues')) {
            DB::table('zone_migration_issues')->truncate();
        }
    }

    /**
     * Assemble a consistent issue payload for the logging table.
     */
    private function issueRow(string $table, int $id, ?string $zoneCode, string $note): array
    {
        return [
            'table_name' => $table,
            'record_id'  => $id,
            'zone_code'  => $zoneCode,
            'note'       => Str::limit($note, 255),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Persist all collected migration issues in one batch.
     */
    private function storeIssues(array $issues): void
    {
        if ($issues === [] || ! Schema::hasTable('zone_migration_issues')) {
            return;
        }

        DB::table('zone_migration_issues')->insert($issues);
    }
};
