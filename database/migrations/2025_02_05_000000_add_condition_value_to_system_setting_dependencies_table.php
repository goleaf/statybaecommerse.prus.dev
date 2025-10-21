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
        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            // Store the parsed operator and value separately for improved querying.
            $table->string('condition_operator', 100)->nullable()->after('condition');
            $table->string('condition_value')->nullable()->after('condition_operator');
        });

        $dependencies = DB::table('system_setting_dependencies')->select('id', 'condition')->get();

        foreach ($dependencies as $dependency) {
            $condition = $dependency->condition;

            $operator = null;
            $value = null;

            if (! is_null($condition)) {
                if (is_string($condition)) {
                    $decoded = json_decode($condition, true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $operator = $decoded['operator'] ?? null;
                        $value = $decoded['value'] ?? null;
                    } else {
                        $operator = $condition;
                    }
                } elseif (is_array($condition)) {
                    $operator = $condition['operator'] ?? null;
                    $value = $condition['value'] ?? null;
                }
            }

            DB::table('system_setting_dependencies')
                ->where('id', $dependency->id)
                ->update([
                    'condition_operator' => $operator,
                    'condition_value' => is_array($value) ? json_encode($value) : $value,
                ]);
        }

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            $table->dropColumn('condition');
        });

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            // The operator now lives in the `condition` column directly.
            $table->string('condition', 100)->nullable()->after('depends_on_setting_id');
        });

        $dependencies = DB::table('system_setting_dependencies')->select('id', 'condition_operator')->get();

        foreach ($dependencies as $dependency) {
            DB::table('system_setting_dependencies')
                ->where('id', $dependency->id)
                ->update(['condition' => $dependency->condition_operator]);
        }

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            $table->dropColumn('condition_operator');
        });
    }

    public function down(): void
    {
        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            $table->json('condition_json')->nullable()->after('depends_on_setting_id');
        });

        $dependencies = DB::table('system_setting_dependencies')
            ->select('id', 'condition', 'condition_value')
            ->get();

        foreach ($dependencies as $dependency) {
            $operator = $dependency->condition;
            $value = $dependency->condition_value;

            $payload = null;

            if (! is_null($operator)) {
                $decodedValue = json_decode((string) $value, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $value = $decodedValue;
                }

                $payload = json_encode([
                    'operator' => $operator,
                    'value' => $value,
                ]);
            }

            DB::table('system_setting_dependencies')
                ->where('id', $dependency->id)
                ->update(['condition_json' => $payload]);
        }

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            $table->dropColumn('condition');
        });

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            $table->json('condition')->nullable()->after('depends_on_setting_id');
        });

        $dependencies = DB::table('system_setting_dependencies')
            ->select('id', 'condition_json')
            ->get();

        foreach ($dependencies as $dependency) {
            DB::table('system_setting_dependencies')
                ->where('id', $dependency->id)
                ->update(['condition' => $dependency->condition_json]);
        }

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            $table->dropColumn('condition_json');
            $table->dropColumn('condition_value');
        });
    }
};
