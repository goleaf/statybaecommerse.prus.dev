<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('suppliers') && Schema::hasTable('partners')) {
            $usedPartnerIds = $this->collectUsedPartnerIds();

            if ($usedPartnerIds !== []) {
                $this->backfillSuppliers($usedPartnerIds);
            }
        }

        $this->migrateInventorySupplierForeignKey();
    }

    public function down(): void
    {
        $this->restoreInventoryPartnerForeignKey();
    }

    /**
     * @return array<int, int>
     */
    private function collectUsedPartnerIds(): array
    {
        $partnerIds = collect();

        if (Schema::hasTable('variant_inventories') && Schema::hasColumn('variant_inventories', 'supplier_id')) {
            $partnerIds = $partnerIds->merge(
                DB::table('variant_inventories')
                    ->whereNotNull('supplier_id')
                    ->distinct()
                    ->pluck('supplier_id')
                    ->map(static fn (mixed $id): int => (int) $id)
                    ->all()
            );
        }

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'partner_id')) {
            $partnerIds = $partnerIds->merge(
                DB::table('orders')
                    ->whereNotNull('partner_id')
                    ->distinct()
                    ->pluck('partner_id')
                    ->map(static fn (mixed $id): int => (int) $id)
                    ->all()
            );
        }

        return $partnerIds
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @param array<int, int> $partnerIds
     */
    private function backfillSuppliers(array $partnerIds): void
    {
        $partners = DB::table('partners')
            ->whereIn('id', $partnerIds)
            ->select([
                'id',
                'name',
                'code',
                'contact_email',
                'contact_phone',
                'metadata',
                'is_enabled',
                'created_at',
                'updated_at',
                'deleted_at',
            ])
            ->orderBy('id')
            ->get();

        if ($partners->isEmpty()) {
            return;
        }

        $knownCodes = DB::table('suppliers')
            ->pluck('code')
            ->filter(static fn (mixed $code): bool => is_string($code) && trim($code) !== '')
            ->map(static fn (string $code): string => Str::upper(trim($code)))
            ->values()
            ->all();

        foreach ($partners as $partner) {
            $partnerId = (int) $partner->id;
            $partnerName = $this->nullableString($partner->name) ?? "Supplier {$partnerId}";

            $supplierPayload = [
                'name' => $partnerName,
                'code' => $this->generateSupplierCode(
                    preferred: $this->nullableString($partner->code),
                    name: $partnerName,
                    id: $partnerId,
                    knownCodes: $knownCodes,
                ),
                'contact_email' => $this->nullableString($partner->contact_email),
                'contact_phone' => $this->nullableString($partner->contact_phone),
                'notes'         => $this->extractNotes($partner->metadata),
                'is_enabled'    => (bool) ($partner->is_enabled ?? true),
                'created_at'    => $this->normalizeTimestamp($partner->created_at),
                'updated_at'    => $this->normalizeTimestamp($partner->updated_at),
                'deleted_at'    => $this->normalizeNullableTimestamp($partner->deleted_at),
            ];

            DB::table('suppliers')->updateOrInsert(
                ['id' => $partnerId],
                $supplierPayload,
            );
        }
    }

    /**
     * @param array<int, string> $knownCodes
     */
    private function generateSupplierCode(?string $preferred, string $name, int $id, array &$knownCodes): string
    {
        $base = Str::upper(Str::slug($preferred ?: $name, '-'));

        if ($base === '') {
            $base = 'SUPPLIER';
        }

        $suffix = str_pad((string) max(1, $id), 3, '0', STR_PAD_LEFT);
        $candidate = "{$base}-{$suffix}";
        $index = 1;

        while (in_array($candidate, $knownCodes, true) &&
            ! DB::table('suppliers')->where('id', $id)->where('code', $candidate)->exists()) {
            $candidate = "{$base}-{$suffix}-{$index}";
            $index++;
        }

        $knownCodes[] = $candidate;

        return $candidate;
    }

    private function migrateInventorySupplierForeignKey(): void
    {
        if (! Schema::hasTable('variant_inventories') ||
            ! Schema::hasColumn('variant_inventories', 'supplier_id') ||
            ! Schema::hasTable('suppliers')) {
            return;
        }

        // Any orphaned partner references become null before we enforce supplier FK.
        DB::table('variant_inventories')
            ->whereNotNull('supplier_id')
            ->whereNotIn('supplier_id', static function (Builder $query): void {
                $query->from('suppliers')->select('id');
            })
            ->update(['supplier_id' => null]);

        $this->dropForeignIfExists('variant_inventories', ['supplier_id']);

        try {
            Schema::table('variant_inventories', function (Blueprint $table): void {
                $table->foreign('supplier_id')
                    ->references('id')
                    ->on('suppliers')
                    ->nullOnDelete();
            });
        } catch (Throwable) {
            // Some database drivers (especially SQLite during historical schema runs)
            // may reject FK alterations. In that case we keep the existing schema.
        }
    }

    private function restoreInventoryPartnerForeignKey(): void
    {
        if (! Schema::hasTable('variant_inventories') ||
            ! Schema::hasColumn('variant_inventories', 'supplier_id') ||
            ! Schema::hasTable('partners')) {
            return;
        }

        $this->dropForeignIfExists('variant_inventories', ['supplier_id']);

        try {
            Schema::table('variant_inventories', function (Blueprint $table): void {
                $table->foreign('supplier_id')
                    ->references('id')
                    ->on('partners')
                    ->nullOnDelete();
            });
        } catch (Throwable) {
            // Keep down migration resilient across environments with limited FK support.
        }
    }

    /**
     * @param array<int, string> $columns
     */
    private function dropForeignIfExists(string $tableName, array $columns): void
    {
        try {
            Schema::table($tableName, function (Blueprint $table) use ($columns): void {
                $table->dropForeign($columns);
            });
        } catch (Throwable) {
            // Ignore if the unnamed/array-style FK drop is unsupported.
        }

        if ($columns === []) {
            return;
        }

        $foreignName = $tableName . '_' . implode('_', $columns) . '_foreign';

        try {
            Schema::table($tableName, function (Blueprint $table) use ($foreignName): void {
                $table->dropForeign($foreignName);
            });
        } catch (Throwable) {
            // Ignore if FK name does not exist in this schema revision.
        }
    }

    private function extractNotes(mixed $metadata): ?string
    {
        if (is_array($metadata)) {
            $notes = $metadata['notes'] ?? null;

            return $this->nullableString($notes);
        }

        if (! is_string($metadata) || trim($metadata) === '') {
            return null;
        }

        try {
            /** @var mixed $decoded */
            $decoded = json_decode($metadata, true, 512, JSON_THROW_ON_ERROR);

            if (is_array($decoded)) {
                $notes = $decoded['notes'] ?? null;

                return $this->nullableString($notes);
            }
        } catch (Throwable) {
            // Keep original metadata text as notes when it is not valid JSON.
            return $this->nullableString($metadata);
        }

        return null;
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed !== '' ? $trimmed : null;
    }

    private function normalizeTimestamp(mixed $value): Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if (is_string($value) && trim($value) !== '') {
            try {
                return Carbon::parse($value);
            } catch (Throwable) {
                // Fall through to now() if parsing fails.
            }
        }

        return now();
    }

    private function normalizeNullableTimestamp(mixed $value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        if (is_string($value) && trim($value) !== '') {
            try {
                return Carbon::parse($value);
            } catch (Throwable) {
                return null;
            }
        }

        return null;
    }
};
