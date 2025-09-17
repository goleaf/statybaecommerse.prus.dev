<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach ([
            'sh_addresses',
            'sh_attribute_values',
            'sh_attributes',
            'sh_customer_group_user',
            'sh_customer_groups',
            'sh_product_attributes',
        ] as $tableName) {
            Schema::dropIfExists($tableName);
        }
        Schema::enableForeignKeyConstraints();

        Schema::create('product_analytics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('date');
            $table->integer('views')->default(0);
            $table->integer('cart_additions')->default(0);
            $table->integer('purchases')->default(0);
            $table->integer('wishlist_additions')->default(0);
            $table->decimal('conversion_rate', 5, 4)->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'date']);
            $table->index(['date', 'views']);
            $table->index(['date', 'purchases']);
        });

        Schema::create('system_notifications', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_dismissible')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->index(['type', 'created_at']);
            $table->index(['expires_at']);
        });

        Schema::create('search_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('query');
            $table->integer('results_count')->default(0);
            $table->string('ip_address')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->json('filters')->nullable();
            $table->timestamp('searched_at');
            $table->timestamps();

            $table->index(['query']);
            $table->index(['searched_at']);
            $table->index(['results_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_logs');
        Schema::dropIfExists('system_notifications');
        Schema::dropIfExists('product_analytics');
    }
};
