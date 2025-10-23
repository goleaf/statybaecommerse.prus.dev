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
        Schema::table('feature_flags', function (Blueprint $table): void {
            if (! Schema::hasColumn('feature_flags', 'created_by_id')) {
                $table->unsignedBigInteger('created_by_id')->nullable()->after('approval_notes');
            }

            if (! Schema::hasColumn('feature_flags', 'updated_by_id')) {
                $table->unsignedBigInteger('updated_by_id')->nullable()->after('created_by_id');
            }
        });

        $this->migrateExistingAttribution();

        if (Schema::hasColumn('feature_flags', 'created_by')) {
            Schema::table('feature_flags', function (Blueprint $table): void {
                $table->dropColumn('created_by');
            });
        }

        if (Schema::hasColumn('feature_flags', 'updated_by')) {
            Schema::table('feature_flags', function (Blueprint $table): void {
                $table->dropColumn('updated_by');
            });
        }

        Schema::table('feature_flags', function (Blueprint $table): void {
            if (! Schema::hasColumn('feature_flags', 'created_by')) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->after('approval_notes')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('feature_flags', 'updated_by')) {
                $table->foreignId('updated_by')
                    ->nullable()
                    ->after('created_by')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        DB::statement('UPDATE feature_flags SET created_by = created_by_id, updated_by = updated_by_id');

        Schema::table('feature_flags', function (Blueprint $table): void {
            if (Schema::hasColumn('feature_flags', 'created_by_id')) {
                $table->dropColumn('created_by_id');
            }

            if (Schema::hasColumn('feature_flags', 'updated_by_id')) {
                $table->dropColumn('updated_by_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('feature_flags', function (Blueprint $table): void {
            $table->string('created_by_temp')->nullable()->after('approval_notes');
            $table->string('updated_by_temp')->nullable()->after('created_by_temp');
        });

        DB::table('feature_flags')->orderBy('id')->chunkById(100, function ($flags): void {
            foreach ($flags as $flag) {
                DB::table('feature_flags')
                    ->where('id', $flag->id)
                    ->update([
                        'created_by_temp' => $this->resolveLegacyString($flag->created_by, $flag->metadata, 'legacy_created_by'),
                        'updated_by_temp' => $this->resolveLegacyString($flag->updated_by, $flag->metadata, 'legacy_updated_by'),
                    ]);
            }
        });

        Schema::table('feature_flags', function (Blueprint $table): void {
            if (Schema::hasColumn('feature_flags', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }

            if (Schema::hasColumn('feature_flags', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }
        });

        Schema::table('feature_flags', function (Blueprint $table): void {
            $table->string('created_by')->nullable()->after('approval_notes');
            $table->string('updated_by')->nullable()->after('created_by');
        });

        DB::statement('UPDATE feature_flags SET created_by = created_by_temp, updated_by = updated_by_temp');

        Schema::table('feature_flags', function (Blueprint $table): void {
            $table->dropColumn(['created_by_temp', 'updated_by_temp']);
        });
    }

    private function migrateExistingAttribution(): void
    {
        $cache = [];

        DB::table('feature_flags')
            ->select('id', 'created_by', 'updated_by', 'metadata')
            ->orderBy('id')
            ->chunkById(100, function ($flags) use (&$cache): void {
                foreach ($flags as $flag) {
                    $createdById = $this->resolveUserId($flag->created_by, $cache);
                    $updatedById = $this->resolveUserId($flag->updated_by, $cache);

                    $metadata = $this->decodeMetadata($flag->metadata);
                    $metadataUpdated = false;

                    if ($flag->created_by !== null && $createdById === null) {
                        $metadataUpdated = true;
                        $metadata['legacy_created_by'] = $flag->created_by;
                    }

                    if ($flag->updated_by !== null && $updatedById === null) {
                        $metadataUpdated = true;
                        $metadata['legacy_updated_by'] = $flag->updated_by;
                    }

                    $update = [
                        'created_by_id' => $createdById,
                        'updated_by_id' => $updatedById,
                    ];

                    if ($metadataUpdated) {
                        $update['metadata'] = $this->encodeMetadata($metadata);
                    }

                    DB::table('feature_flags')
                        ->where('id', $flag->id)
                        ->update($update);
                }
            });
    }

    private function resolveUserId(mixed $value, array &$cache): ?int
    {
        if ($value === null) {
            return null;
        }

        $stringValue = trim((string) $value);

        if ($stringValue === '') {
            return null;
        }

        if (array_key_exists($stringValue, $cache)) {
            return $cache[$stringValue];
        }

        $userId = null;

        if (ctype_digit($stringValue)) {
            $userId = DB::table('users')->where('id', (int) $stringValue)->value('id');
        }

        if ($userId === null && Str::contains($stringValue, '@')) {
            $userId = DB::table('users')->where('email', $stringValue)->value('id');
        }

        if ($userId === null) {
            $userId = DB::table('users')->where('name', $stringValue)->value('id');
        }

        if ($userId === null && preg_match('/(\d+)/', $stringValue, $matches)) {
            $userId = DB::table('users')->where('id', (int) $matches[1])->value('id');
        }

        $cache[$stringValue] = $userId ? (int) $userId : null;

        return $cache[$stringValue];
    }

    private function resolveLegacyString(mixed $value, mixed $metadata, string $legacyKey): ?string
    {
        if ($value !== null) {
            $user = DB::table('users')->where('id', $value)->first(['email', 'name', 'id']);

            if ($user !== null) {
                return (string) ($user->email ?? $user->name ?? $user->id);
            }

            return (string) $value;
        }

        $metadataArray = $this->decodeMetadata($metadata);

        if (array_key_exists($legacyKey, $metadataArray)) {
            return (string) $metadataArray[$legacyKey];
        }

        return null;
    }

    private function decodeMetadata(mixed $metadata): array
    {
        if (is_array($metadata)) {
            return $metadata;
        }

        if (is_string($metadata) && $metadata !== '') {
            $decoded = json_decode($metadata, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    private function encodeMetadata(array $metadata): string
    {
        $encoded = json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $encoded === false ? json_encode($metadata) ?: '{}' : $encoded;
    }
};
