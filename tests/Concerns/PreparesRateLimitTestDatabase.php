<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait PreparesRateLimitTestDatabase
{
    protected function bootRateLimitTestEnvironment(): void
    {
        $this->registerSanctumMiddleware();
        $this->rebuildRateLimitSupportTables();
    }

    private function registerSanctumMiddleware(): void
    {
        app()->register(\Laravel\Sanctum\SanctumServiceProvider::class);

        $router = app('router');
        $aliases = $router->getMiddleware();

        if (! array_key_exists('abilities', $aliases)) {
            $router->aliasMiddleware('abilities', \Laravel\Sanctum\Http\Middleware\CheckAbilities::class);
        }

        if (! array_key_exists('ability', $aliases)) {
            $router->aliasMiddleware('ability', \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class);
        }

        config([
            'auth.guards.sanctum' => [
                'driver' => 'sanctum',
                'provider' => 'users',
            ],
            'sanctum.guard' => [],
        ]);
    }

    private function rebuildRateLimitSupportTables(): void
    {
        $permissionTables = config('permission.table_names');

        $tablesToDrop = [
            $permissionTables['model_has_permissions'],
            $permissionTables['role_has_permissions'],
            $permissionTables['model_has_roles'],
            $permissionTables['permissions'],
            $permissionTables['roles'],
            'user_wishlists',
            'products',
            'reviews',
            'orders',
            'addresses',
            'cart_items',
            'activity_log',
            'personal_access_tokens',
            'users',
        ];

        // Temporarily relax foreign key checks so SQLite does not block the schema reset during rate limit tests.
        Schema::disableForeignKeyConstraints();

        try {
            foreach ($tablesToDrop as $table) {
                Schema::dropIfExists($table);
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('preferred_locale')->nullable();
            $table->string('timezone')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table): void {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_log', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description')->nullable();
            $table->nullableMorphs('subject');
            $table->nullableMorphs('causer');
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->string('event')->nullable();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('session_id')->index();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('addresses', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('type')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('postal_code')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_shipping')->default(false);
            $table->boolean('is_billing')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('status')->nullable();
            $table->decimal('total', 12, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('user_wishlists', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create($permissionTables['roles'], function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create($permissionTables['permissions'], function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create($permissionTables['role_has_permissions'], function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->primary(['permission_id', 'role_id']);
        });

        $modelKey = config('permission.column_names.model_morph_key', 'model_id');

        Schema::create($permissionTables['model_has_roles'], function (Blueprint $table) use ($modelKey): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger($modelKey);
            $table->index([$modelKey, 'model_type'], 'model_has_roles_model_id_model_type_index');
        });

        Schema::create($permissionTables['model_has_permissions'], function (Blueprint $table) use ($modelKey): void {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger($modelKey);
            $table->index([$modelKey, 'model_type'], 'model_has_permissions_model_id_model_type_index');
        });
    }
}
