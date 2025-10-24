<?php

declare(strict_types=1);

namespace Tests\Feature\Api\RateLimiting\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait InteractsWithRateLimitSchema
{
    protected function migrateRateLimitSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('exports');
        Schema::dropIfExists('users');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('user_wishlists');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('model_has_roles');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('preferred_locale', 10)->nullable();
            $table->boolean('is_admin')->default(false);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('exports', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->nullable();
            $table->string('format', 20);
            $table->string('status', 50);
            $table->string('exportable_type')->nullable();
            $table->json('columns')->nullable();
            $table->json('exportable_options')->nullable();
            $table->unsignedBigInteger('total_rows')->nullable();
            $table->unsignedBigInteger('processed_rows')->nullable();
            $table->string('artifact_disk')->nullable();
            $table->string('artifact_path')->nullable();
            $table->string('artifact_filename')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->timestamps();
        });

        Schema::create('addresses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('type')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('city')->nullable();
            $table->string('address_line_1')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status')->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('sku')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_wishlists', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::enableForeignKeyConstraints();
    }

    protected function resetRateLimitSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('user_wishlists');
        Schema::dropIfExists('products');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('exports');
        Schema::dropIfExists('users');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('roles');

        Schema::enableForeignKeyConstraints();
    }
}
