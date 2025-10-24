<?php declare(strict_types=1);

namespace Tests\Feature\Api\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\Support\TestingDatabase;

trait UsesApiRateLimitSchema
{
    protected function setUpRateLimitSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('user_wishlists');
        Schema::dropIfExists('products');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('exports');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('preferred_locale')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
        });

        Schema::create('addresses', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('address_line_1')->nullable();
            $table->string('city')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('postal_code')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('number')->nullable();
            $table->string('status')->default('completed');
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->string('status')->default('published');
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('user_wishlists', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');
            $table->string('name')->default('Default Wishlist');
            $table->boolean('is_default')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id')->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('exports', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('format');
            $table->string('status');
            $table->string('exportable_type')->nullable();
            $table->json('columns')->nullable();
            $table->json('exportable_options')->nullable();
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->string('artifact_disk')->nullable();
            $table->string('artifact_path')->nullable();
            $table->string('artifact_filename')->nullable();
            $table->unsignedInteger('total_rows')->nullable();
            $table->unsignedInteger('processed_rows')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->foreign('requested_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::create('activity_log', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description')->nullable();
            $table->nullableMorphs('subject');
            $table->nullableMorphs('causer');
            $table->uuid('batch_uuid')->nullable();
            $table->string('event')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    protected function tearDownRateLimitSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('user_wishlists');
        Schema::dropIfExists('products');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('exports');
        Schema::dropIfExists('users');

        Schema::enableForeignKeyConstraints();

        // Reset the shared testing database so other suites that expect the canonical
        // migration set (bootstrapped via RefreshDatabase) are not left without core tables.
        TestingDatabase::teardown();
        TestingDatabase::migrate();
    }
}
