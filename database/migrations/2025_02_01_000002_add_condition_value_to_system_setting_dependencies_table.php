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
            // Promote the parsed operator/value columns so we can migrate any existing JSON
            // payloads without relying on driver-specific schema diffs.
            $table->string('condition_operator')->nullable()->after('depends_on_setting_id');
            $table->text('condition_value')->nullable()->after('condition_operator');
        });

        DB::table('system_setting_dependencies')
            ->select(['id', 'condition'])
            ->orderBy('id')
            ->chunkById(200, function ($dependencies): void {
                foreach ($dependencies as $dependency) {
                    /**
                     * @var object{id:int, condition:mixed|null} $dependency
                     */
                    $operator = null;
                    $value = null;

                    if ($dependency->condition !== null) {
                        if (is_string($dependency->condition)) {
                            // Decode the legacy JSON payloads persisted by earlier releases so we can
                            // split the operator and comparison value into first-class columns.
                            $decoded = json_decode($dependency->condition, true);
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
                        } elseif (is_array($dependency->condition)) {
                            $operator = $dependency->condition['operator'] ?? null;
                            $value = $dependency->condition['value'] ?? null;
                        } elseif (is_scalar($dependency->condition)) {
                            $operator = (string) $dependency->condition;
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
            Schema::table('system_setting_dependencies', function (Blueprint $table): void {
                $table->dropColumn('condition');
            });
        }

        if (! Schema::hasColumn('system_setting_dependencies', 'condition')) {
            Schema::table('system_setting_dependencies', function (Blueprint $table): void {
                $table->string('condition')->nullable()->after('depends_on_setting_id');
            });
        }

        DB::table('system_setting_dependencies')->update([
            'condition' => DB::raw('condition_operator'),
        ]);

        // @phpstan-ignore-next-line The column is created above when the guard lets execution continue.
        if (Schema::hasColumn('system_setting_dependencies', 'condition_operator')) {
            Schema::table('system_setting_dependencies', function (Blueprint $table): void {
                $table->dropColumn('condition_operator');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('system_setting_dependencies')) {
            return;
        }

        if (! Schema::hasColumn('system_setting_dependencies', 'condition_json')) {
            Schema::table('system_setting_dependencies', function (Blueprint $table): void {
                $table->json('condition_json')->nullable()->after('depends_on_setting_id');
            });
        }

        DB::table('system_setting_dependencies')
            ->select(['id', 'condition', 'condition_value'])
            ->orderBy('id')
            ->chunkById(200, function ($dependencies): void {
                foreach ($dependencies as $dependency) {
                    /**
                     * @var object{id:int, condition:mixed|null, condition_value:mixed|null} $dependency
                     */
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
            Schema::table('system_setting_dependencies', function (Blueprint $table): void {
                $table->dropColumn('condition');
            });
        }

        if (! Schema::hasColumn('system_setting_dependencies', 'condition')) {
            Schema::table('system_setting_dependencies', function (Blueprint $table): void {
                $table->json('condition')->nullable()->after('depends_on_setting_id');
            });
        }

        DB::table('system_setting_dependencies')->update([
            'condition' => DB::raw('condition_json'),
        ]);

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            // Drop the temporary JSON payload and restored value column when rolling back the
            // migration. Individual guards are cheaper than separate table calls.
            if (Schema::hasColumn('system_setting_dependencies', 'condition_json')) {
                $table->dropColumn('condition_json');
            }

            if (Schema::hasColumn('system_setting_dependencies', 'condition_value')) {
                $table->dropColumn('condition_value');
            }
        });
    }
};
