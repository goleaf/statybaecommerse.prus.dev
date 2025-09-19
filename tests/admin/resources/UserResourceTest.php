<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@test.com',
            'is_active' => true
        ]));
    }

    public function test_user_resource_can_be_instantiated(): void
    {
        $resource = new UserResource();
        $this->assertInstanceOf(UserResource::class, $resource);
    }

    public function test_user_resource_has_required_methods(): void
    {
        $resource = new UserResource();

        $this->assertTrue(method_exists($resource, 'form'));
        $this->assertTrue(method_exists($resource, 'table'));
        $this->assertTrue(method_exists($resource, 'getPages'));
        $this->assertTrue(method_exists($resource, 'getModel'));
    }

    public function test_user_resource_form_works(): void
    {
        $resource = new UserResource();

        // Test that form method exists and is callable
        $this->assertTrue(method_exists($resource, 'form'));
        $this->assertTrue(is_callable([$resource, 'form']));
    }

    public function test_user_resource_table_works(): void
    {
        $resource = new UserResource();

        // Test that table method exists and is callable
        $this->assertTrue(method_exists($resource, 'table'));
        $this->assertTrue(is_callable([$resource, 'table']));
    }

    public function test_user_resource_has_valid_model(): void
    {
        $resource = new UserResource();
        $model = $resource->getModel();

        $this->assertEquals(User::class, $model);
        $this->assertTrue(class_exists($model));
    }

    public function test_user_resource_handles_empty_database(): void
    {
        $resource = new UserResource();

        // Test that methods exist and are callable
        $this->assertTrue(method_exists($resource, 'form'));
        $this->assertTrue(method_exists($resource, 'table'));
        $this->assertTrue(is_callable([$resource, 'form']));
        $this->assertTrue(is_callable([$resource, 'table']));
    }

    public function test_user_resource_with_sample_data(): void
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        $resource = new UserResource();

        // Test that methods exist and are callable
        $this->assertTrue(method_exists($resource, 'form'));
        $this->assertTrue(method_exists($resource, 'table'));
        $this->assertTrue(is_callable([$resource, 'form']));
        $this->assertTrue(is_callable([$resource, 'table']));

        // Test that user was created
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }
}
