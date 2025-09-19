<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SimpleAttributeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_can_create_attribute(): void
    {
        $attribute = Attribute::factory()->create([
            'name' => 'Test Attribute',
            'type' => 'text',
            'is_active' => true,
        ]);

        $this->assertEquals('Test Attribute', $attribute->name);
        $this->assertEquals('text', $attribute->type);
        $this->assertTrue($attribute->is_active);
    }

    public function test_can_filter_attributes_by_type(): void
    {
        Attribute::factory()->create(['type' => 'select']);
        Attribute::factory()->create(['type' => 'text']);

        $selectAttributes = Attribute::where('type', 'select')->get();
        $textAttributes = Attribute::where('type', 'text')->get();

        $this->assertCount(1, $selectAttributes);
        $this->assertCount(1, $textAttributes);
    }
}

