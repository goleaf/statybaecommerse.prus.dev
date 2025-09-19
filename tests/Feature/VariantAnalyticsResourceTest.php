<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\VariantAnalytics;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class VariantAnalyticsResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user for testing
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_list_variant_analytics(): void
    {
        // Arrange
        $variant = ProductVariant::factory()->create();
        VariantAnalytics::factory()->count(5)->withVariant($variant)->create();

        // Act & Assert
        Livewire::test(\App\Filament\Resources\VariantAnalyticsResource\Pages\ListVariantAnalytics::class)
            ->assertCanSeeTableRecords(VariantAnalytics::all());
    }

    public function test_can_create_variant_analytics(): void
    {
        // Arrange
        $variant = ProductVariant::factory()->create();
        $data = [
            'variant_id' => $variant->id,
            'date' => now()->toDateString(),
            'views' => 100,
            'clicks' => 50,
            'add_to_cart' => 25,
            'purchases' => 10,
            'revenue' => 500.00,
            'conversion_rate' => 10.0,
        ];

        // Act
        Livewire::test(\App\Filament\Resources\VariantAnalyticsResource\Pages\CreateVariantAnalytics::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        // Assert
        $this->assertDatabaseHas('variant_analytics', $data);
    }

    public function test_can_edit_variant_analytics(): void
    {
        // Arrange
        $variant = ProductVariant::factory()->create();
        $analytics = VariantAnalytics::factory()->withVariant($variant)->create();
        
        $updatedData = [
            'views' => 200,
            'clicks' => 100,
            'revenue' => 1000.00,
        ];

        // Act
        Livewire::test(\App\Filament\Resources\VariantAnalyticsResource\Pages\EditVariantAnalytics::class, [
            'record' => $analytics->getRouteKey(),
        ])
            ->fillForm($updatedData)
            ->call('save')
            ->assertHasNoFormErrors();

        // Assert
        $analytics->refresh();
        $this->assertEquals(200, $analytics->views);
        $this->assertEquals(100, $analytics->clicks);
        $this->assertEquals(1000.00, $analytics->revenue);
    }

    public function test_can_view_variant_analytics(): void
    {
        // Arrange
        $variant = ProductVariant::factory()->create();
        $analytics = VariantAnalytics::factory()->withVariant($variant)->create();

        // Act & Assert
        Livewire::test(\App\Filament\Resources\VariantAnalyticsResource\Pages\ViewVariantAnalytics::class, [
            'record' => $analytics->getRouteKey(),
        ])
            ->assertOk();
    }

    public function test_can_delete_variant_analytics(): void
    {
        // Arrange
        $variant = ProductVariant::factory()->create();
        $analytics = VariantAnalytics::factory()->withVariant($variant)->create();

        // Act
        Livewire::test(\App\Filament\Resources\VariantAnalyticsResource\Pages\ListVariantAnalytics::class)
            ->callTableAction('delete', $analytics);

        // Assert
        $this->assertDatabaseMissing('variant_analytics', ['id' => $analytics->id]);
    }

    public function test_can_filter_by_variant(): void
    {
        // Arrange
        $variant1 = ProductVariant::factory()->create();
        $variant2 = ProductVariant::factory()->create();
        
        $analytics1 = VariantAnalytics::factory()->withVariant($variant1)->create();
        $analytics2 = VariantAnalytics::factory()->withVariant($variant2)->create();

        // Act & Assert
        Livewire::test(\App\Filament\Resources\VariantAnalyticsResource\Pages\ListVariantAnalytics::class)
            ->filterTable('variant_id', $variant1->id)
            ->assertCanSeeTableRecords([$analytics1])
            ->assertCanNotSeeTableRecords([$analytics2]);
    }

    public function test_can_filter_by_date_range(): void
    {
        // Arrange
        $variant = ProductVariant::factory()->create();
        
        $analytics1 = VariantAnalytics::factory()
            ->withVariant($variant)
            ->forDate(now()->subDays(5)->toDateString())
            ->create();
            
        $analytics2 = VariantAnalytics::factory()
            ->withVariant($variant)
            ->forDate(now()->subDays(15)->toDateString())
            ->create();

        // Act & Assert
        Livewire::test(\App\Filament\Resources\VariantAnalyticsResource\Pages\ListVariantAnalytics::class)
            ->filterTable('date_range', [
                'date_from' => now()->subDays(10)->toDateString(),
                'date_until' => now()->toDateString(),
            ])
            ->assertCanSeeTableRecords([$analytics1])
            ->assertCanNotSeeTableRecords([$analytics2]);
    }

    public function test_can_filter_high_performing_analytics(): void
    {
        // Arrange
        $variant = ProductVariant::factory()->create();
        
        $highPerforming = VariantAnalytics::factory()
            ->highPerforming()
            ->withVariant($variant)
            ->create();
            
        $lowPerforming = VariantAnalytics::factory()
            ->lowPerforming()
            ->withVariant($variant)
            ->create();

        // Act & Assert
        Livewire::test(\App\Filament\Resources\VariantAnalyticsResource\Pages\ListVariantAnalytics::class)
            ->filterTable('high_performing')
            ->assertCanSeeTableRecords([$highPerforming])
            ->assertCanNotSeeTableRecords([$lowPerforming]);
    }

    public function test_can_filter_low_performing_analytics(): void
    {
        // Arrange
        $variant = ProductVariant::factory()->create();
        
        $highPerforming = VariantAnalytics::factory()
            ->highPerforming()
            ->withVariant($variant)
            ->create();
            
        $lowPerforming = VariantAnalytics::factory()
            ->lowPerforming()
            ->withVariant($variant)
            ->create();

        // Act & Assert
        Livewire::test(\App\Filament\Resources\VariantAnalyticsResource\Pages\ListVariantAnalytics::class)
            ->filterTable('low_performing')
            ->assertCanSeeTableRecords([$lowPerforming])
            ->assertCanNotSeeTableRecords([$highPerforming]);
    }

    public function test_calculated_metrics_are_displayed_correctly(): void
    {
        // Arrange
        $variant = ProductVariant::factory()->create();
        $analytics = VariantAnalytics::factory()
            ->withVariant($variant)
            ->create([
                'views' => 1000,
                'clicks' => 100,
                'add_to_cart' => 50,
                'purchases' => 10,
            ]);

        // Act & Assert
        Livewire::test(\App\Filament\Resources\VariantAnalyticsResource\Pages\ListVariantAnalytics::class)
            ->assertCanSeeTableRecords([$analytics]);
    }

    public function test_form_validation_requires_variant(): void
    {
        // Act & Assert
        Livewire::test(\App\Filament\Resources\VariantAnalyticsResource\Pages\CreateVariantAnalytics::class)
            ->fillForm([
                'date' => now()->toDateString(),
                'views' => 100,
            ])
            ->call('create')
            ->assertHasFormErrors(['variant_id']);
    }

    public function test_form_validation_requires_date(): void
    {
        // Arrange
        $variant = ProductVariant::factory()->create();

        // Act & Assert
        Livewire::test(\App\Filament\Resources\VariantAnalyticsResource\Pages\CreateVariantAnalytics::class)
            ->fillForm([
                'variant_id' => $variant->id,
                'views' => 100,
            ])
            ->call('create')
            ->assertHasFormErrors(['date']);
    }

    public function test_form_validation_numeric_fields(): void
    {
        // Arrange
        $variant = ProductVariant::factory()->create();

        // Act & Assert
        Livewire::test(\App\Filament\Resources\VariantAnalyticsResource\Pages\CreateVariantAnalytics::class)
            ->fillForm([
                'variant_id' => $variant->id,
                'date' => now()->toDateString(),
                'views' => 'not_a_number',
                'clicks' => 'invalid',
            ])
            ->call('create')
            ->assertHasFormErrors(['views', 'clicks']);
    }

    public function test_bulk_delete_analytics(): void
    {
        // Arrange
        $variant = ProductVariant::factory()->create();
        $analytics = VariantAnalytics::factory()->count(3)->withVariant($variant)->create();

        // Act
        Livewire::test(\App\Filament\Resources\VariantAnalyticsResource\Pages\ListVariantAnalytics::class)
            ->callTableBulkAction('delete', $analytics);

        // Assert
        $this->assertDatabaseCount('variant_analytics', 0);
    }

    public function test_table_sorts_by_date_desc_by_default(): void
    {
        // Arrange
        $variant = ProductVariant::factory()->create();
        
        $oldAnalytics = VariantAnalytics::factory()
            ->withVariant($variant)
            ->forDate(now()->subDays(10)->toDateString())
            ->create();
            
        $newAnalytics = VariantAnalytics::factory()
            ->withVariant($variant)
            ->forDate(now()->toDateString())
            ->create();

        // Act & Assert
        $component = Livewire::test(\App\Filament\Resources\VariantAnalyticsResource\Pages\ListVariantAnalytics::class);
        
        $records = $component->get('tableRecords');
        $this->assertEquals($newAnalytics->id, $records->first()->id);
        $this->assertEquals($oldAnalytics->id, $records->last()->id);
    }

    public function test_table_polls_for_updates(): void
    {
        // Arrange
        $variant = ProductVariant::factory()->create();
        VariantAnalytics::factory()->withVariant($variant)->create();

        // Act & Assert
        Livewire::test(\App\Filament\Resources\VariantAnalyticsResource\Pages\ListVariantAnalytics::class)
            ->assertOk();
    }
}
