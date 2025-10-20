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

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            $table->string('condition_operator')->nullable()->after('depends_on_setting_id');
            $table->text('condition_value')->nullable()->after('condition_operator');
        });

        DB::table('system_setting_dependencies')
            ->select(['id', 'condition'])
            ->orderBy('id')
            ->chunkById(200, function ($dependencies): void {
                foreach ($dependencies as $dependency) {
                    $operator = null;
                    $value = null;

                    if ($dependency->condition !== null) {
                        $decoded = json_decode((string) $dependency->condition, true);
                        $jsonError = json_last_error();

                        if ($jsonError === JSON_ERROR_NONE) {
                            if (is_array($decoded)) {
                                $operator = $decoded['operator'] ?? null;
                                $value = $decoded['value'] ?? null;
                            } elseif (is_scalar($decoded)) {
                                $operator = (string) $decoded;
                            }
                        } elseif (is_string($dependency->condition)) {
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
            $table->dropColumn('condition');
        });

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            $table->string('condition')->nullable()->after('depends_on_setting_id');
        });

        DB::table('system_setting_dependencies')->update([
            'condition' => DB::raw('condition_operator'),
        ]);

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            $table->dropColumn('condition_operator');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('system_setting_dependencies')) {
            return;
        }

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            $table->json('condition_json')->nullable()->after('depends_on_setting_id');
        });

        DB::table('system_setting_dependencies')
            ->select(['id', 'condition', 'condition_value'])
            ->orderBy('id')
            ->chunkById(200, function ($dependencies): void {
                foreach ($dependencies as $dependency) {
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
            $table->dropColumn('condition');
        });

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            $table->json('condition')->nullable()->after('depends_on_setting_id');
        });

        DB::table('system_setting_dependencies')->update([
            'condition' => DB::raw('condition_json'),
        ]);

        Schema::table('system_setting_dependencies', function (Blueprint $table): void {
            $table->dropColumn('condition_json');
            $table->dropColumn('condition_value');
        });
    }
};
