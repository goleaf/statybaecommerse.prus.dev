<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('system_setting_dependencies')) {
            return;
        }

        if (
            Schema::hasColumn('system_setting_dependencies', 'condition_value') ||
            Schema::hasColumn('system_setting_dependencies', 'condition_operator')
        ) {
            // Bail out when a later patch already introduced the new schema columns. Without
            // this guard SQLite would attempt to add duplicate columns during migrate:fresh,
            // causing the test harness to drop back to the minimal fallback schema.
            return;
        }

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            if (!Schema::hasColumn('system_setting_dependencies', 'condition_operator')) {
                // Guard the operator column so repeated migrate:fresh cycles in tests
                // do not attempt to add it twice when SQLite snapshots linger.
                $table->string('condition_operator')->nullable()->after('depends_on_setting_id');
            }

            if (!Schema::hasColumn('system_setting_dependencies', 'condition_value')) {
                // Apply the same protection to the value column to keep the migration
                // idempotent across partially upgraded environments.
                $table->text('condition_value')->nullable()->after('condition_operator');
            }
        });

        DB::table('system_setting_dependencies')
            ->select(['id', 'condition'])
            ->orderBy('id')
            ->chunkById(200, function ($dependencies): void {
                foreach ($dependencies as $dependency) {
                    /** @var object{id:int,condition:mixed} $dependency */
                    $operator = null;
                    $value = null;

                    if ($dependency->condition !== null) {
                        if (is_string($dependency->condition)) {
                            $decoded = json_decode($dependency->condition, true);
                        } elseif (is_numeric($dependency->condition) || is_bool($dependency->condition)) {
                            $decoded = $dependency->condition;
                        } else {
                            $decoded = null;
                        }

                        $jsonError = json_last_error();

                        if ($jsonError === JSON_ERROR_NONE) {
                            if (is_array($decoded)) {
                                $operator = $decoded['operator'] ?? null;
                                $value = $decoded['value'] ?? null;
                            } elseif (is_scalar($decoded)) {
                                $operator = (string) $decoded;
                            }
                        } else {
                            $operator = $dependency->condition;
                        }

                        if (is_array($value) || is_object($value)) {
                            $value = json_encode($value, JSON_THROW_ON_ERROR);
                        }
                    }

                    DB::table('system_setting_dependencies')
                        ->where('id', $dependency->id)
                        ->update([
                            'condition_operator' => $operator,
                            'condition_value' => $value,
                        ]);
                }
            });

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            if (Schema::hasColumn('system_setting_dependencies', 'condition')) {
                // Drop the original JSON column only when it still exists to avoid
                // double-drop failures on already patched databases.
                DB::statement('DROP INDEX IF EXISTS system_setting_dependencies_condition_index');
                $table->dropColumn('condition');
            }
        });

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            if (!Schema::hasColumn('system_setting_dependencies', 'condition')) {
                // Recreate the normalised string column when required so the schema
                // matches production after the data backfill completes.
                $table->string('condition')->nullable()->after('depends_on_setting_id');
            }
        });

        DB::table('system_setting_dependencies')->update([
            'condition' => DB::raw('condition_operator'),
        ]);

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            if (Schema::hasColumn('system_setting_dependencies', 'condition_operator')) {
                // Clean up the temporary column when it remains so future migrations
                // can run without colliding with manual clean-ups.
                $table->dropColumn('condition_operator');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('system_setting_dependencies')) {
            return;
        }

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            if (!Schema::hasColumn('system_setting_dependencies', 'condition_json')) {
                // Mirror the guard in the `up` path so rolling back remains safe
                // for partially migrated databases.
                $table->json('condition_json')->nullable()->after('depends_on_setting_id');
            }
        });

        DB::table('system_setting_dependencies')
            ->select(['id', 'condition', 'condition_value'])
            ->orderBy('id')
            ->chunkById(200, function ($dependencies): void {
                foreach ($dependencies as $dependency) {
                    /** @var object{id:int,condition:mixed,condition_value:mixed} $dependency */
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
                        'value' => $decodedValue,
                    ];

                    DB::table('system_setting_dependencies')
                        ->where('id', $dependency->id)
                        ->update([
                            'condition_json' => json_encode($payload, JSON_THROW_ON_ERROR),
                        ]);
                }
            });

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            if (Schema::hasColumn('system_setting_dependencies', 'condition')) {
                DB::statement('DROP INDEX IF EXISTS system_setting_dependencies_condition_index');
                $table->dropColumn('condition');
            }
        });

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            if (!Schema::hasColumn('system_setting_dependencies', 'condition')) {
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
