<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('suppliers')) {
            return;
        }

        Schema::table('suppliers', function (Blueprint $table): void {
            if (! Schema::hasColumn('suppliers', 'company_code')) {
                $table->string('company_code')->nullable()->after('name');
            }

            if (! Schema::hasColumn('suppliers', 'vat_code')) {
                $table->string('vat_code')->nullable()->after('company_code');
            }

            if (! Schema::hasColumn('suppliers', 'contact_person')) {
                $table->string('contact_person')->nullable()->after('code');
            }

            if (! Schema::hasColumn('suppliers', 'website')) {
                $table->string('website')->nullable()->after('contact_phone');
            }

            if (! Schema::hasColumn('suppliers', 'address')) {
                $table->string('address')->nullable()->after('website');
            }

            if (! Schema::hasColumn('suppliers', 'city')) {
                $table->string('city')->nullable()->after('address');
            }

            if (! Schema::hasColumn('suppliers', 'postal_code')) {
                $table->string('postal_code')->nullable()->after('city');
            }

            if (! Schema::hasColumn('suppliers', 'country')) {
                $table->string('country')->nullable()->after('postal_code');
            }
        });

        $this->backfillCompanyCodes();
        $this->normalizeSystemCodes();

        try {
            Schema::table('suppliers', function (Blueprint $table): void {
                if (Schema::hasColumn('suppliers', 'company_code')) {
                    $table->string('company_code')->nullable(false)->change();
                }
            });
        } catch (Throwable) {
            // Gracefully degrade for drivers that do not support altering nullability.
        }

        try {
            Schema::table('suppliers', function (Blueprint $table): void {
                if (Schema::hasColumn('suppliers', 'company_code')) {
                    $table->index('company_code');
                }
            });
        } catch (Throwable) {
            // Skip duplicate/unsupported index changes on non-standard schemas.
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('suppliers')) {
            return;
        }

        try {
            Schema::table('suppliers', function (Blueprint $table): void {
                $table->dropIndex(['company_code']);
            });
        } catch (Throwable) {
            // Ignore missing index in down migrations.
        }

        Schema::table('suppliers', function (Blueprint $table): void {
            foreach (['country', 'postal_code', 'city', 'address', 'website', 'contact_person', 'vat_code', 'company_code'] as $column) {
                if (Schema::hasColumn('suppliers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function backfillCompanyCodes(): void
    {
        if (! Schema::hasColumn('suppliers', 'company_code')) {
            return;
        }

        $suppliers = DB::table('suppliers')
            ->select(['id', 'name', 'code', 'company_code'])
            ->orderBy('id')
            ->get();

        foreach ($suppliers as $supplier) {
            $candidate = $this->normalizeCompanyCode($supplier->company_code)
                ?? $this->normalizeCompanyCode($supplier->code)
                ?? $this->companyCodeFromName((string) $supplier->name, (int) $supplier->id);

            DB::table('suppliers')
                ->where('id', $supplier->id)
                ->update(['company_code' => $candidate]);
        }
    }

    private function normalizeSystemCodes(): void
    {
        $suppliers = DB::table('suppliers')
            ->select(['id', 'name', 'code'])
            ->orderBy('id')
            ->get();

        $usedCodes = [];

        foreach ($suppliers as $supplier) {
            $base = Str::slug((string) ($supplier->code ?: $supplier->name ?: 'supplier'));

            if ($base === '') {
                $base = 'supplier';
            }

            $candidate = $base;
            $suffix = 2;

            while (isset($usedCodes[$candidate])) {
                $candidate = sprintf('%s-%d', $base, $suffix);
                $suffix++;
            }

            $usedCodes[$candidate] = true;

            DB::table('suppliers')
                ->where('id', $supplier->id)
                ->update(['code' => $candidate]);
        }
    }

    private function normalizeCompanyCode(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        $compact = preg_replace('/\s+/', '', $trimmed);

        if (! is_string($compact) || $compact === '') {
            return null;
        }

        return Str::upper($compact);
    }

    private function companyCodeFromName(string $name, int $id): string
    {
        $base = Str::upper(Str::slug($name, ''));

        if ($base === '') {
            $base = 'SUPPLIER';
        }

        return sprintf('%s%03d', $base, max(1, $id));
    }
};
