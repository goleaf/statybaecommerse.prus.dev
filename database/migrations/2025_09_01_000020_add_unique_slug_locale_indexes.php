<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tables = [
            'sh_brand_translations' => 'sh_brand_translations_locale_slug_unique',
            'sh_category_translations' => 'sh_category_translations_locale_slug_unique',
            'sh_collection_translations' => 'sh_collection_translations_locale_slug_unique',
            'sh_product_translations' => 'sh_product_translations_locale_slug_unique',
            'sh_legal_translations' => 'sh_legal_translations_locale_slug_unique',
        ];
        foreach ($tables as $tableName => $indexName) {
            if (Schema::hasTable($tableName)) {
                try {
                    DB::statement("ALTER TABLE `{$tableName}` ADD UNIQUE `{$indexName}` (`locale`, `slug`)");
                } catch (\Throwable $e) {
                    // ignore if already exists or unsupported
                }
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'sh_brand_translations' => 'sh_brand_translations_locale_slug_unique',
            'sh_category_translations' => 'sh_category_translations_locale_slug_unique',
            'sh_collection_translations' => 'sh_collection_translations_locale_slug_unique',
            'sh_product_translations' => 'sh_product_translations_locale_slug_unique',
            'sh_legal_translations' => 'sh_legal_translations_locale_slug_unique',
        ];
        foreach ($tables as $tableName => $indexName) {
            if (Schema::hasTable($tableName)) {
                try {
                    DB::statement("ALTER TABLE `{$tableName}` DROP INDEX `{$indexName}`");
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        }
    }
};
