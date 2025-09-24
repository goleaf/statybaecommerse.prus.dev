<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('system_setting_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('system_setting_id')->constrained('system_settings')->cascadeOnDelete();
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->string('change_reason')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
            $table->index(['system_setting_id', 'changed_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_setting_histories');
    }
};
