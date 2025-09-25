<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('display_name')->nullable();
                $table->text('value')->nullable();
                $table->string('type')->default('string');
                $table->string('group')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_public')->default(false);
                $table->boolean('is_required')->default(false);
                $table->boolean('is_encrypted')->default(false);
                $table->boolean('is_active')->default(true)->after('is_encrypted');
                $table->timestamps();
                $table->index(['group', 'key']);
                $table->index('is_public');
            });
        } else {
            Schema::table('settings', function (Blueprint $table) {
                if (! Schema::hasColumn('settings', 'display_name')) {
                    $table->string('display_name')->nullable()->after('key');
                }
                if (! Schema::hasColumn('settings', 'group')) {
                    $table->string('group')->nullable()->after('type');
                }
                if (! Schema::hasColumn('settings', 'is_required')) {
                    $table->boolean('is_required')->default(false)->after('is_public');
                }
                if (! Schema::hasColumn('settings', 'is_encrypted')) {
                    $table->boolean('is_encrypted')->default(false)->after('is_required');
                }
                if (! Schema::hasColumn('settings', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('is_encrypted');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'is_encrypted')) {
                $table->dropColumn('is_encrypted');
            }
            if (Schema::hasColumn('settings', 'is_required')) {
                $table->dropColumn('is_required');
            }
            if (Schema::hasColumn('settings', 'group')) {
                $table->dropColumn('group');
            }
            if (Schema::hasColumn('settings', 'display_name')) {
                $table->dropColumn('display_name');
            }
        });
    }
};
