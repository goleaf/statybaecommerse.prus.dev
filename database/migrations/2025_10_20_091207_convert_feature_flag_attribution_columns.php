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
        Schema::table('feature_flags', function (Blueprint $table): void {
            if ($this->foreignKeyExists('feature_flags', 'feature_flags_created_by_foreign')) {
                $table->dropForeign('feature_flags_created_by_foreign');
            }

            if ($this->indexExists('feature_flags', 'feature_flags_created_by_foreign')) {
                $table->dropIndex('feature_flags_created_by_foreign');
            }

            if ($this->foreignKeyExists('feature_flags', 'feature_flags_updated_by_foreign')) {
                $table->dropForeign('feature_flags_updated_by_foreign');
            }

            if ($this->indexExists('feature_flags', 'feature_flags_updated_by_foreign')) {
                $table->dropIndex('feature_flags_updated_by_foreign');
            }
        });

        Schema::table('feature_flags', function (Blueprint $table): void {
            if (Schema::hasColumn('feature_flags', 'created_by') && ! Schema::hasColumn('feature_flags', 'created_by_name')) {
                $table->renameColumn('created_by', 'created_by_name');
            }

            if (Schema::hasColumn('feature_flags', 'updated_by') && ! Schema::hasColumn('feature_flags', 'updated_by_name')) {
                $table->renameColumn('updated_by', 'updated_by_name');
            }
        });

        Schema::table('feature_flags', function (Blueprint $table): void {
            $shouldAddCreatedBy = ! Schema::hasColumn('feature_flags', 'created_by');

            if ($shouldAddCreatedBy) {
                $createdByColumn = $table->foreignId('created_by')->nullable();

                if ($afterColumn = $this->getCreatedByPositionColumn()) {
                    $createdByColumn->after($afterColumn);
                }

                if (! $this->foreignKeyExists('feature_flags', 'feature_flags_created_by_foreign')) {
                    $createdByColumn
                        ->constrained('users')
                        ->nullOnDelete();
                }
            }

            if (! Schema::hasColumn('feature_flags', 'updated_by')) {
                $updatedByColumn = $table->foreignId('updated_by')->nullable();

                if ($afterColumn = $this->getUpdatedByPositionColumn($shouldAddCreatedBy)) {
                    $updatedByColumn->after($afterColumn);
                }

                if (! $this->foreignKeyExists('feature_flags', 'feature_flags_updated_by_foreign')) {
                    $updatedByColumn
                        ->constrained('users')
                        ->nullOnDelete();
                }
            }

            if (Schema::hasColumn('feature_flags', 'created_by_name')) {
                $table->index('created_by_name');
            }

            if (Schema::hasColumn('feature_flags', 'updated_by_name')) {
                $table->index('updated_by_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('feature_flags', function (Blueprint $table): void {
            if (Schema::hasColumn('feature_flags', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }

            if (Schema::hasColumn('feature_flags', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }

            if (Schema::hasColumn('feature_flags', 'updated_by_name')) {
                $table->dropIndex('feature_flags_updated_by_name_index');
            }

            if (Schema::hasColumn('feature_flags', 'created_by_name')) {
                $table->dropIndex('feature_flags_created_by_name_index');
            }
        });

        Schema::table('feature_flags', function (Blueprint $table): void {
            if (Schema::hasColumn('feature_flags', 'updated_by_name') && ! Schema::hasColumn('feature_flags', 'updated_by')) {
                $table->renameColumn('updated_by_name', 'updated_by');
            }

            if (Schema::hasColumn('feature_flags', 'created_by_name') && ! Schema::hasColumn('feature_flags', 'created_by')) {
                $table->renameColumn('created_by_name', 'created_by');
            }
        });
    }

    private function getCreatedByPositionColumn(): ?string
    {
        return $this->getFirstExistingColumn([
            'created_by_name',
            'approval_notes',
            'approval_status',
            'success_metrics',
            'rollback_plan',
            'rollout_strategy',
            'impact_level',
            'category',
            'priority',
            'metadata',
            'end_date',
            'start_date',
            'ends_at',
            'starts_at',
            'environment',
            'rollout_percentage',
            'conditions',
            'description',
        ]);
    }

    private function getUpdatedByPositionColumn(bool $shouldAddCreatedBy): ?string
    {
        if ($shouldAddCreatedBy || Schema::hasColumn('feature_flags', 'created_by')) {
            return 'created_by';
        }

        return $this->getFirstExistingColumn([
            'created_by_name',
            'approval_notes',
            'approval_status',
            'success_metrics',
            'rollback_plan',
            'rollout_strategy',
            'impact_level',
            'category',
            'priority',
            'metadata',
            'end_date',
            'start_date',
            'ends_at',
            'starts_at',
            'environment',
            'rollout_percentage',
            'conditions',
            'description',
        ]);
    }

    private function getFirstExistingColumn(array $columns): ?string
    {
        foreach ($columns as $column) {
            if (Schema::hasColumn('feature_flags', $column)) {
                return $column;
            }
        }

        return null;
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            $database = $connection->getDatabaseName();

            $result = $connection->selectOne(
                'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ?',
                [$database, $table, $constraint, 'FOREIGN KEY']
            );

            return $result !== null;
        }

        if ($driver === 'sqlite') {
            $result = DB::select("PRAGMA foreign_key_list('{$table}')");

            foreach ($result as $row) {
                $constraintName = null;

                if (is_object($row) && property_exists($row, 'constraint_name')) {
                    $constraintName = $row->constraint_name;
                } elseif (is_array($row) && array_key_exists('constraint_name', $row)) {
                    $constraintName = $row['constraint_name'];
                }

                if ($constraintName === $constraint) {
                    return true;
                }

                $idValue = null;

                if (is_object($row) && property_exists($row, 'id')) {
                    $idValue = (string) $row->id;
                } elseif (is_array($row) && array_key_exists('id', $row)) {
                    $idValue = (string) $row['id'];
                }

                if ($idValue === $constraint) {
                    return true;
                }
            }
        }

        return false;
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            $result = $connection->selectOne(
                'SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1',
                [$connection->getDatabaseName(), $table, $index]
            );

            return $result !== null;
        }

        if ($driver === 'sqlite') {
            $result = DB::select("PRAGMA index_list('{$table}')");

            foreach ($result as $row) {
                $indexName = null;

                if (is_object($row) && property_exists($row, 'name')) {
                    $indexName = $row->name;
                } elseif (is_array($row) && array_key_exists('name', $row)) {
                    $indexName = $row['name'];
                }

                if ($indexName === $index) {
                    return true;
                }
            }
        }

        return false;
    }
};
