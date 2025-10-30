<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cities')) {
            return;
        }

        if (! $this->indexExists('cities', 'cities_country_id_index')) {
            Schema::table('cities', function (Blueprint $table): void {
                $table->index('country_id', 'cities_country_id_index');
            });
        }

        if (! $this->indexExists('cities', 'cities_country_id_slug_unique')) {
            Schema::table('cities', function (Blueprint $table): void {
                $table->unique(['country_id', 'slug'], 'cities_country_id_slug_unique');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('cities')) {
            return;
        }

        if ($this->indexExists('cities', 'cities_country_id_slug_unique')) {
            Schema::table('cities', function (Blueprint $table): void {
                $table->dropUnique('cities_country_id_slug_unique');
            });
        }

        if ($this->indexExists('cities', 'cities_country_id_index')) {
            Schema::table('cities', function (Blueprint $table): void {
                $table->dropIndex('cities_country_id_index');
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = DB::connection()->getDriverName();

        if ($connection === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");

            foreach ($indexes as $indexEntry) {
                if (($indexEntry->name ?? null) === $index) {
                    return true;
                }
            }

            return false;
        }

        $schemaManager = Schema::getConnection()->getDoctrineSchemaManager();
        $doctrineTable = $schemaManager->listTableDetails($table);

        return $doctrineTable->hasIndex($index);
    }
};
