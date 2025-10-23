<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

                if (Schema::hasColumn('feature_flags', 'created_by_name')) {
                    $createdByColumn->after('created_by_name');
                } elseif (Schema::hasColumn('feature_flags', 'approval_notes')) {
                    $createdByColumn->after('approval_notes');
                }

                $createdByColumn
                    ->constrained('users')
                    ->nullOnDelete();

                if ($afterColumn = $this->getCreatedByPositionColumn()) {
                    $createdByColumn->after($afterColumn);
                }
            }

            if (! Schema::hasColumn('feature_flags', 'updated_by')) {
                $updatedByColumn = $table->foreignId('updated_by')->nullable();

                if ($shouldAddCreatedBy) {
                    $updatedByColumn->after('created_by');
                } elseif (Schema::hasColumn('feature_flags', 'created_by')) {
                    $updatedByColumn->after('created_by');
                } elseif (Schema::hasColumn('feature_flags', 'created_by_name')) {
                    $updatedByColumn->after('created_by_name');
                } elseif (Schema::hasColumn('feature_flags', 'approval_notes')) {
                    $updatedByColumn->after('approval_notes');
                }

                $updatedByColumn
                    ->constrained('users')
                    ->nullOnDelete();
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
        if (Schema::hasColumn('feature_flags', 'created_by_name')) {
            return 'created_by_name';
        }

        if (Schema::hasColumn('feature_flags', 'approval_notes')) {
            return 'approval_notes';
        }

        return null;
    }
};
