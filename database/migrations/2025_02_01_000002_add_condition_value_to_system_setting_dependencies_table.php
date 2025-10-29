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
        if (! Schema::hasTable('system_setting_dependencies')) {
            return;
        }

        $shouldAddConditionOperator = ! Schema::hasColumn('system_setting_dependencies', 'condition_operator');
        $shouldAddConditionValue = ! Schema::hasColumn('system_setting_dependencies', 'condition_value');

        Schema::table('system_setting_dependencies', function (Blueprint $table) use ($shouldAddConditionOperator, $shouldAddConditionValue): void {
            if ($shouldAddConditionOperator) {
                // SQLite will attempt to re-run this migration whenever the test harness
                // provisions a fresh database. Guarding the column creation keeps repeated
                // refreshes idempotent while still adding the operator field on installs
                // that were missing it previously.
                $table->string('condition_operator')->nullable()->after('depends_on_setting_id');
            }

            if ($shouldAddConditionValue) {
                // Earlier schema snapshots already ship with a dedicated `condition_value`
                // column. Only add it when the table genuinely lacks the field so SQLite's
                // duplicate-column error never interrupts automated migrations.
                $table->text('condition_value')->nullable()->after('condition_operator');
            }
        });

        DB::table('system_setting_dependencies')
            ->select(['id', 'condition'])
            ->orderBy('id')
            ->chunkById(200, function ($dependencies): void {
                foreach ($dependencies as $dependency) {
                    /** @var object{id:int,condition:mixed|null} $dependency */
                    $operator = null;
                    $value = null;

                    $rawCondition = $dependency->condition;

                    if ($rawCondition !== null) {
                        if (is_string($rawCondition)) {
                            $decoded = json_decode($rawCondition, true);
                            $jsonError = json_last_error();

                            if ($jsonError === JSON_ERROR_NONE) {
                                if (is_array($decoded)) {
                                    $operator = $decoded['operator'] ?? null;
                                    $value = $decoded['value'] ?? null;
                                } elseif (is_scalar($decoded)) {
                                    $operator = (string) $decoded;
                                }
                            } else {
                                $operator = $rawCondition;
                            }
                        } elseif (is_array($rawCondition)) {
                            $operator = $rawCondition['operator'] ?? null;
                            $value = $rawCondition['value'] ?? null;
                        } elseif (is_scalar($rawCondition)) {
                            $operator = (string) $rawCondition;
                        }

                        if (is_array($value) || is_object($value)) {
                            $value = json_encode($value, JSON_THROW_ON_ERROR);
                        }
                    }

                    DB::table('system_setting_dependencies')
                        ->where('id', $dependency->id)
                        ->update([
                            'condition_operator' => $operator,
                            'condition_value'    => $value,
                        ]);
                }
            });

        if (Schema::hasColumn('system_setting_dependencies', 'condition')) {
            try {
                Schema::table('system_setting_dependencies', function (Blueprint $table): void {
                    // Drop the existing index so SQLite can safely rebuild the table when
                    // removing the column. Attempting to drop the column first triggers
                    // a failure because the index would reference a missing field.
                    $table->dropIndex('system_setting_dependencies_condition_index');
                });
            } catch (Throwable) {
                // Ignore drivers that cannot introspect indexes here; the follow-up column
                // removal will still succeed if the index was already absent.
            }

            Schema::table('system_setting_dependencies', function (Blueprint $table): void {
                // Ensure we only remove the legacy JSON column when it still exists;
                // some downstream environments already removed it in earlier patches.
                $table->dropColumn('condition');
            });
        }

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            if (! Schema::hasColumn('system_setting_dependencies', 'condition')) {
                // Re-create the scalar `condition` column so query scopes keep storing
                // the operator string without breaking older forms that expect it.
                $table->string('condition')->nullable()->after('depends_on_setting_id');
            }
        });

        try {
            Schema::table('system_setting_dependencies', function (Blueprint $table): void {
                // Rebuild the original index to preserve query performance for condition
                // lookups now that the column has been recreated as a scalar string.
                $table->index('condition');
            });
        } catch (Throwable) {
            // Swallow duplicate-index errors that can appear when the structure already
            // included the expected index (for example on subsequent deployments).
        }

        DB::table('system_setting_dependencies')->update([
            'condition' => DB::raw('condition_operator'),
        ]);

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            if (Schema::hasColumn('system_setting_dependencies', 'condition_operator')) {
                // Drop the temporary helper column once the value has been copied
                // back to `condition`, mirroring the original migration intent.
                $table->dropColumn('condition_operator');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('system_setting_dependencies')) {
            return;
        }

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            if (! Schema::hasColumn('system_setting_dependencies', 'condition_json')) {
                // Mirror the forward migration guard so repeated rollbacks in SQLite do
                // not attempt to recreate the same helper column twice.
                $table->json('condition_json')->nullable()->after('depends_on_setting_id');
            }
        });

        DB::table('system_setting_dependencies')
            ->select(['id', 'condition', 'condition_value'])
            ->orderBy('id')
            ->chunkById(200, function ($dependencies): void {
                foreach ($dependencies as $dependency) {
                    /** @var object{id:int,condition:mixed|null,condition_value:mixed|null} $dependency */
                    $value = $dependency->condition_value;
                    $decodedValue = null;

                    if (is_string($value)) {
                        $decodedValue = json_decode($value, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $decodedValue = $value;
                        }
                    } elseif ($value !== null) {
                        $decodedValue = $value;
                    }

                    $payload = [
                        'operator' => $dependency->condition,
                        'value'    => $decodedValue,
                    ];

                    DB::table('system_setting_dependencies')
                        ->where('id', $dependency->id)
                        ->update([
                            'condition_json' => json_encode($payload, JSON_THROW_ON_ERROR),
                        ]);
                }
            });

        if (Schema::hasColumn('system_setting_dependencies', 'condition')) {
            try {
                Schema::table('system_setting_dependencies', function (Blueprint $table): void {
                    $table->dropIndex('system_setting_dependencies_condition_index');
                });
            } catch (Throwable) {
                // Index absence is acceptable when earlier migrations already cleaned it up.
            }

            Schema::table('system_setting_dependencies', function (Blueprint $table): void {
                $table->dropColumn('condition');
            });
        }

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            if (! Schema::hasColumn('system_setting_dependencies', 'condition')) {
                $table->json('condition')->nullable()->after('depends_on_setting_id');
            }
        });

        DB::table('system_setting_dependencies')->update([
            'condition' => DB::raw('condition_json'),
        ]);

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            if (Schema::hasColumn('system_setting_dependencies', 'condition_json')) {
                $table->dropColumn('condition_json');
            }

            if (Schema::hasColumn('system_setting_dependencies', 'condition_value')) {
                $table->dropColumn('condition_value');
            }
        });
    }
};
