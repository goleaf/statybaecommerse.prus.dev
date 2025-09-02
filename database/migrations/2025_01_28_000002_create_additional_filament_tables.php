<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create notifications table for Filament database notifications
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        // Create failed_imports table for Filament import tracking
        if (!Schema::hasTable('failed_imports')) {
            Schema::create('failed_imports', function (Blueprint $table) {
                $table->id();
                $table->string('import_type');
                $table->json('data');
                $table->text('error_message');
                $table->timestamps();
                
                $table->index('import_type');
            });
        }

        // Create export_batches table for Filament export tracking
        if (!Schema::hasTable('export_batches')) {
            Schema::create('export_batches', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('file_disk');
                $table->string('file_name');
                $table->unsignedInteger('total_rows')->default(0);
                $table->unsignedInteger('processed_rows')->default(0);
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                
                $table->index('completed_at');
            });
        }

        // Create user_preferences table for admin panel customization
        if (!Schema::hasTable('user_preferences')) {
            Schema::create('user_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('key');
                $table->json('value')->nullable();
                $table->timestamps();
                
                $table->unique(['user_id', 'key']);
            });
        }

        // Add additional columns to existing tables for better Filament integration
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'avatar_url')) {
                    $table->string('avatar_url')->nullable()->after('email');
                }
                if (!Schema::hasColumn('users', 'last_login_at')) {
                    $table->timestamp('last_login_at')->nullable()->after('email_verified_at');
                }
                if (!Schema::hasColumn('users', 'timezone')) {
                    $table->string('timezone')->default('UTC')->after('preferred_locale');
                }
                if (!Schema::hasColumn('users', 'is_admin')) {
                    $table->boolean('is_admin')->default(false)->after('is_active');
                }
            });
        }

        // Add metadata columns to key tables for better admin experience
        $tables = ['products', 'categories', 'brands', 'collections'];
        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'admin_notes')) {
                        $table->text('admin_notes')->nullable()->after('updated_at');
                    }
                    if (!Schema::hasColumn($tableName, 'metadata')) {
                        $table->json('metadata')->nullable()->after('admin_notes');
                    }
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('export_batches');
        Schema::dropIfExists('failed_imports');
        Schema::dropIfExists('notifications');

        $tables = ['users', 'products', 'categories', 'brands', 'collections'];
        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if ($tableName === 'users') {
                        $table->dropColumn(['avatar_url', 'last_login_at', 'timezone', 'is_admin']);
                    } else {
                        $table->dropColumn(['admin_notes', 'metadata']);
                    }
                });
            }
        }
    }
};
