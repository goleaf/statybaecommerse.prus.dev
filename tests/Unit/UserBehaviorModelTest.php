<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\UserBehavior;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * UserBehavior Model Unit Test
 *
 * Unit test for UserBehavior model functionality without Filament dependencies.
 */
final class UserBehaviorModelTest extends TestCase
{
    private string $databasePath;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        putenv('TEST_FORCE_MINIMAL_SQLITE=true');
        $_ENV['TEST_FORCE_MINIMAL_SQLITE'] = 'true';
        $_SERVER['TEST_FORCE_MINIMAL_SQLITE'] = 'true';
    }

    public static function tearDownAfterClass(): void
    {
        unset($_ENV['TEST_FORCE_MINIMAL_SQLITE'], $_SERVER['TEST_FORCE_MINIMAL_SQLITE']);
        putenv('TEST_FORCE_MINIMAL_SQLITE');

        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $storageDirectory = storage_path('testing/databases');

        if (! is_dir($storageDirectory)) {
            mkdir($storageDirectory, 0o755, true);
        }

        $this->databasePath = $storageDirectory . '/user_behavior_' . Str::random(12) . '.sqlite';

        touch($this->databasePath);
        chmod($this->databasePath, 0o666);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->databasePath);
        config()->set('database.connections.sqlite.prefix', '');
        config()->set('database.connections.sqlite.foreign_key_constraints', true);

        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        DB::reconnect('sqlite');

        Schema::connection('sqlite')->enableForeignKeyConstraints();

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        Schema::connection('sqlite')->disableForeignKeyConstraints();
        Schema::dropIfExists('user_behaviors');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('products');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
        // Drop permission tables in reverse dependency order to keep
        // foreign key constraints satisfied during teardown.
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');

        DB::disconnect('sqlite');

        if (isset($this->databasePath) && is_file($this->databasePath)) {
            @unlink($this->databasePath);
        }

        parent::tearDown();
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            // Store decomposed name parts to mirror the production schema so
            // the model factory can persist deterministic first/last names.
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('preferred_locale', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_admin')->default(false);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('roles', function (Blueprint $table): void {
            // Minimal role schema satisfies the Spatie permission hooks that
            // auto-assign the super_admin role during unit tests.
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('permissions', function (Blueprint $table): void {
            // Provide a lean permission table so cache warmups do not fail
            // when the registrar inspects configured permissions.
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_roles', function (Blueprint $table): void {
            // Pivot table mirrors the package definition allowing syncRoles()
            // to persist assignments against the temporary SQLite schema.
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->unsignedBigInteger('model_id');
            $table->string('model_type');
            $table->index(['model_id', 'model_type']);
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table): void {
            // Companion pivot keeps permission lookups working when the
            // registrar attempts to hydrate cached relationships.
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->unsignedBigInteger('model_id');
            $table->string('model_type');
            $table->index(['model_id', 'model_type']);
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::create('role_has_permissions', function (Blueprint $table): void {
            // Bridge roles to permissions so the simplified schema stays
            // compatible with the permission registrar expectations.
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('brands', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('type')->default('simple');
            $table->json('name');
            $table->json('slug')->nullable();
            $table->json('description')->nullable();
            $table->json('short_description')->nullable();
            $table->string('sku')->unique();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(0);
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('manage_stock')->default(true);
            $table->string('status')->default('published');
            $table->json('seo_title')->nullable();
            $table->json('seo_description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
        });

        Schema::create('user_behaviors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('session_id')->nullable();
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->cascadeOnDelete();
            $table->string('behavior_type');
            $table->json('metadata')->nullable();
            $table->string('referrer')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function test_can_create_user_behavior(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $category = Category::factory()->create();

        $userBehavior = UserBehavior::create([
            'user_id'       => $user->id,
            'behavior_type' => 'view',
            'product_id'    => $product->id,
            'category_id'   => $category->id,
            'session_id'    => 'test-session-123',
            'referrer'      => 'https://example.com',
            'user_agent'    => 'Mozilla/5.0 (Test Browser)',
            'ip_address'    => '192.168.1.1',
            'metadata'      => ['test_key' => 'test_value'],
        ]);

        $this->assertDatabaseHas('user_behaviors', [
            'id'            => $userBehavior->id,
            'user_id'       => $user->id,
            'behavior_type' => 'view',
            'product_id'    => $product->id,
            'category_id'   => $category->id,
        ]);

        $this->assertEquals($user->id, $userBehavior->user->id);
        $this->assertEquals($product->id, $userBehavior->product->id);
        $this->assertEquals($category->id, $userBehavior->category->id);
        $this->assertIsArray($userBehavior->metadata);
        $this->assertEquals('test_value', $userBehavior->metadata['test_key']);
    }

    public function test_user_behavior_model_scopes_work(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $category = Category::factory()->create();

        // Create behaviors with different types and dates
        UserBehavior::create([
            'user_id'       => $user->id,
            'behavior_type' => 'view',
            'product_id'    => $product->id,
            'category_id'   => $category->id,
            'created_at'    => now()->subDays(5),
        ]);

        UserBehavior::create([
            'user_id'       => $user->id,
            'behavior_type' => 'click',
            'product_id'    => $product->id,
            'category_id'   => $category->id,
            'created_at'    => now()->subDays(10),
        ]);

        UserBehavior::create([
            'user_id'       => $user->id,
            'behavior_type' => 'purchase',
            'product_id'    => $product->id,
            'category_id'   => $category->id,
            'created_at'    => now()->subDays(35),
        ]);

        // Test scopeRecent
        $recentBehaviors = UserBehavior::recent(30)->get();
        $this->assertCount(2, $recentBehaviors);

        // Test scopeByType
        $viewBehaviors = UserBehavior::byType('view')->get();
        $this->assertCount(1, $viewBehaviors);
        $this->assertEquals('view', $viewBehaviors->first()->behavior_type);

        // Test scopeByUser
        $userBehaviors = UserBehavior::byUser($user->id)->get();
        $this->assertCount(3, $userBehaviors);
    }

    public function test_user_behavior_factory_works(): void
    {
        $userBehavior = UserBehavior::factory()->create([
            'behavior_type' => 'view',
        ]);

        $this->assertInstanceOf(UserBehavior::class, $userBehavior);
        $this->assertEquals('view', $userBehavior->behavior_type);
        $this->assertIsArray($userBehavior->metadata);
    }

    public function test_user_behavior_factory_states_work(): void
    {
        $viewBehavior = UserBehavior::factory()->view()->create();
        $this->assertEquals('view', $viewBehavior->behavior_type);

        $clickBehavior = UserBehavior::factory()->click()->create();
        $this->assertEquals('click', $clickBehavior->behavior_type);

        $addToCartBehavior = UserBehavior::factory()->addToCart()->create();
        $this->assertEquals('add_to_cart', $addToCartBehavior->behavior_type);

        $purchaseBehavior = UserBehavior::factory()->purchase()->create();
        $this->assertEquals('purchase', $purchaseBehavior->behavior_type);

        $searchBehavior = UserBehavior::factory()->search()->create();
        $this->assertEquals('search', $searchBehavior->behavior_type);

        $recentBehavior = UserBehavior::factory()->recent()->create();
        $this->assertTrue($recentBehavior->created_at->isAfter(now()->subDays(8)));

        $todayBehavior = UserBehavior::factory()->today()->create();
        $this->assertTrue($todayBehavior->created_at->isToday());
    }

    public function test_metadata_is_casted_to_array(): void
    {
        $userBehavior = UserBehavior::factory()->create([
            'metadata' => ['key1' => 'value1', 'key2' => 'value2'],
        ]);

        $this->assertIsArray($userBehavior->metadata);
        $this->assertEquals('value1', $userBehavior->metadata['key1']);
        $this->assertEquals('value2', $userBehavior->metadata['key2']);
    }

    public function test_created_at_is_casted_to_datetime(): void
    {
        $userBehavior = UserBehavior::factory()->create();

        $this->assertInstanceOf(\Carbon\Carbon::class, $userBehavior->created_at);
    }

    public function test_timestamps_are_disabled(): void
    {
        $userBehavior = UserBehavior::factory()->create();

        $this->assertNull($userBehavior->updated_at);
    }

    public function test_scope_by_session_works(): void
    {
        $user = User::factory()->create();
        $sessionId = 'test-session-123';

        UserBehavior::factory()->create([
            'user_id'    => $user->id,
            'session_id' => $sessionId,
        ]);

        UserBehavior::factory()->create([
            'user_id'    => $user->id,
            'session_id' => 'different-session',
        ]);

        $sessionBehaviors = UserBehavior::bySession($sessionId)->get();
        $this->assertCount(1, $sessionBehaviors);
        $this->assertEquals($sessionId, $sessionBehaviors->first()->session_id);
    }
}
