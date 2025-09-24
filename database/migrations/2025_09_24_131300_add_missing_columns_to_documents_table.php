<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('documents')) {
            return;
        }

        Schema::table('documents', function (Blueprint $table): void {
            if (!Schema::hasColumn('documents', 'name')) {
                $table->string('name')->nullable()->after('title');
            }
            if (!Schema::hasColumn('documents', 'type')) {
                $table->string('type')->nullable()->after('name');
            }
            if (!Schema::hasColumn('documents', 'version')) {
                $table->string('version')->nullable()->after('type');
            }
            if (!Schema::hasColumn('documents', 'file_size')) {
                $table->unsignedBigInteger('file_size')->nullable()->after('file_path');
            }
            if (!Schema::hasColumn('documents', 'mime_type')) {
                $table->string('mime_type')->nullable()->after('file_size');
            }
            if (!Schema::hasColumn('documents', 'is_public')) {
                $table->boolean('is_public')->default(false)->after('mime_type');
            }
            if (!Schema::hasColumn('documents', 'is_downloadable')) {
                $table->boolean('is_downloadable')->default(true)->after('is_public');
            }
            if (!Schema::hasColumn('documents', 'access_password')) {
                $table->string('access_password')->nullable()->after('is_downloadable');
            }
            if (!Schema::hasColumn('documents', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('generated_at');
            }
            if (!Schema::hasColumn('documents', 'description')) {
                $table->text('description')->nullable()->after('variables');
            }
            if (!Schema::hasColumn('documents', 'notes')) {
                $table->text('notes')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('documents')) {
            return;
        }

        Schema::table('documents', function (Blueprint $table): void {
            foreach (['name', 'type', 'version', 'file_size', 'mime_type', 'is_public', 'is_downloadable', 'access_password', 'expires_at', 'description', 'notes'] as $column) {
                if (Schema::hasColumn('documents', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
