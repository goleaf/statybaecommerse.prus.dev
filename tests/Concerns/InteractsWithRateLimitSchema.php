<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use ReflectionProperty;

trait InteractsWithRateLimitSchema
{
    protected function setUpRateLimitSchema(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('user_wishlists');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('products');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('exports');
        Schema::dropIfExists('users');
        Schema::dropIfExists('activity_log');
        Schema::enableForeignKeyConstraints();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('preferred_locale')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('exports', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->nullable();
            $table->string('format', 32)->default('csv');
            $table->string('status', 32)->default('queued');
            $table->string('exportable_type')->nullable();
            $table->json('columns')->nullable();
            $table->json('exportable_options')->nullable();
            $table->unsignedInteger('total_rows')->nullable();
            $table->unsignedInteger('processed_rows')->nullable();
            $table->string('artifact_disk')->nullable();
            $table->string('artifact_path')->nullable();
            $table->string('artifact_filename')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('activity_log', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->nullableMorphs('causer', 'causer');
            $table->string('event')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
            $table->index('log_name');
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_wishlists', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('addresses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_billing')->default(false);
            $table->boolean('is_shipping')->default(false);
            $table->json('notes')->nullable();
            $table->string('apartment')->nullable();
            $table->string('floor')->nullable();
            $table->string('building')->nullable();
            $table->string('landmark')->nullable();
            $table->json('instructions')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_vat')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->nullable();
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->default(0);
            $table->text('content')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function tearDownRateLimitSchema(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('user_wishlists');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('products');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('exports');
        Schema::dropIfExists('users');
        Schema::dropIfExists('activity_log');
        Schema::enableForeignKeyConstraints();
    }

    protected function clearRateLimiterKeys(string $limiter, string ...$keys): void
    {
        foreach ($keys as $key) {
            RateLimiter::clear($this->formatRateLimiterKey($limiter, $key));
        }
    }

    private function formatRateLimiterKey(string $limiter, string $key): string
    {
        return $this->throttleRequestsShouldHashKeys()
            ? md5($limiter.$key)
            : $limiter.':'.$key;
    }

    private function throttleRequestsShouldHashKeys(): bool
    {
        static $shouldHash;

        if ($shouldHash !== null) {
            return $shouldHash;
        }

        $property = new ReflectionProperty(ThrottleRequests::class, 'shouldHashKeys');
        $property->setAccessible(true);

        $value = $property->getValue();

        return $shouldHash = is_bool($value) ? $value : true;
    }
}
