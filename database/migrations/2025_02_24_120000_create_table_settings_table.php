<?php

declare(strict_types=1);

use App\Models\AdminUser;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('table_settings')) {
            return;
        }

        Schema::create('table_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(AdminUser::class, 'user_id')
                ->constrained('admin_users')
                ->cascadeOnDelete();
            $table->string('resource');
            $table->json('styles')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'resource']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_settings');
    }
};
