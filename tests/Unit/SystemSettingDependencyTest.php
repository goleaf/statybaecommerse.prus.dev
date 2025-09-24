<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\SystemSetting;
use App\Models\SystemSettingDependency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SystemSettingDependencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_system_setting_dependency(): void
    {
        $setting1 = SystemSetting::factory()->create(['key' => 'setting1']);
        $setting2 = SystemSetting::factory()->create(['key' => 'setting2']);

        $dependency = SystemSettingDependency::create([
            'setting_id' => $setting1->id,
            'depends_on_setting_id' => $setting2->id,
            'condition' => 'setting2.value == "enabled"',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('system_setting_dependencies', [
            'id' => $dependency->id,
            'setting_id' => $setting1->id,
            'depends_on_setting_id' => $setting2->id,
            'condition' => 'setting2.value == "enabled"',
            'is_active' => true,
        ]);
    }

    public function test_belongs_to_setting(): void
    {
        $setting = SystemSetting::factory()->create(['key' => 'setting1']);
        $dependency = SystemSettingDependency::factory()->create([
            'setting_id' => $setting->id,
        ]);

        $this->assertInstanceOf(SystemSetting::class, $dependency->setting);
        $this->assertEquals($setting->id, $dependency->setting->id);
    }

    public function test_belongs_to_depends_on_setting(): void
    {
        $setting = SystemSetting::factory()->create(['key' => 'setting1']);
        $dependency = SystemSettingDependency::factory()->create([
            'depends_on_setting_id' => $setting->id,
        ]);

        $this->assertInstanceOf(SystemSetting::class, $dependency->dependsOnSetting);
        $this->assertEquals($setting->id, $dependency->dependsOnSetting->id);
    }

    public function test_can_toggle_active_status(): void
    {
        $dependency = SystemSettingDependency::factory()->create(['is_active' => true]);

        $dependency->update(['is_active' => false]);

        $this->assertFalse($dependency->fresh()->is_active);
    }

    public function test_can_replicate_dependency(): void
    {
        $setting1 = SystemSetting::factory()->create(['key' => 'setting1']);
        $setting2 = SystemSetting::factory()->create(['key' => 'setting2']);

        $dependency = SystemSettingDependency::factory()->create([
            'setting_id' => $setting1->id,
            'depends_on_setting_id' => $setting2->id,
            'condition' => 'setting2.value == "enabled"',
            'is_active' => true,
        ]);

        $replicated = $dependency->replicate();
        $replicated->condition = $dependency->condition.' (Copy)';
        $replicated->is_active = false;
        $replicated->save();

        $this->assertDatabaseHas('system_setting_dependencies', [
            'setting_id' => $setting1->id,
            'depends_on_setting_id' => $setting2->id,
            'condition' => 'setting2.value == "enabled" (Copy)',
            'is_active' => false,
        ]);
    }

    public function test_fillable_attributes(): void
    {
        $dependency = new SystemSettingDependency;

        $fillable = $dependency->getFillable();

        $this->assertContains('setting_id', $fillable);
        $this->assertContains('depends_on_setting_id', $fillable);
        $this->assertContains('condition', $fillable);
        $this->assertContains('is_active', $fillable);
    }

    public function test_casts_attributes(): void
    {
        $dependency = SystemSettingDependency::factory()->create([
            'is_active' => '1',
        ]);

        $this->assertTrue($dependency->is_active);
        $this->assertIsBool($dependency->is_active);
    }

    public function test_scope_active(): void
    {
        $activeDependency = SystemSettingDependency::factory()->create(['is_active' => true]);
        $inactiveDependency = SystemSettingDependency::factory()->create(['is_active' => false]);

        $activeDependencies = SystemSettingDependency::active()->get();

        $this->assertTrue($activeDependencies->contains($activeDependency));
        $this->assertFalse($activeDependencies->contains($inactiveDependency));
    }

    public function test_scope_inactive(): void
    {
        $activeDependency = SystemSettingDependency::factory()->create(['is_active' => true]);
        $inactiveDependency = SystemSettingDependency::factory()->create(['is_active' => false]);

        $inactiveDependencies = SystemSettingDependency::inactive()->get();

        $this->assertFalse($inactiveDependencies->contains($activeDependency));
        $this->assertTrue($inactiveDependencies->contains($inactiveDependency));
    }

    public function test_scope_for_setting(): void
    {
        $setting1 = SystemSetting::factory()->create(['key' => 'setting1']);
        $setting2 = SystemSetting::factory()->create(['key' => 'setting2']);

        $dependency1 = SystemSettingDependency::factory()->create([
            'setting_id' => $setting1->id,
        ]);

        $dependency2 = SystemSettingDependency::factory()->create([
            'setting_id' => $setting2->id,
        ]);

        $dependenciesForSetting1 = SystemSettingDependency::forSetting($setting1->id)->get();

        $this->assertTrue($dependenciesForSetting1->contains($dependency1));
        $this->assertFalse($dependenciesForSetting1->contains($dependency2));
    }

    public function test_scope_depends_on_setting(): void
    {
        $setting1 = SystemSetting::factory()->create(['key' => 'setting1']);
        $setting2 = SystemSetting::factory()->create(['key' => 'setting2']);

        $dependency1 = SystemSettingDependency::factory()->create([
            'depends_on_setting_id' => $setting1->id,
        ]);

        $dependency2 = SystemSettingDependency::factory()->create([
            'depends_on_setting_id' => $setting2->id,
        ]);

        $dependenciesDependsOnSetting1 = SystemSettingDependency::dependsOnSetting($setting1->id)->get();

        $this->assertTrue($dependenciesDependsOnSetting1->contains($dependency1));
        $this->assertFalse($dependenciesDependsOnSetting1->contains($dependency2));
    }

    public function test_scope_with_condition(): void
    {
        $dependency1 = SystemSettingDependency::factory()->create([
            'condition' => 'setting.value == "enabled"',
        ]);

        $dependency2 = SystemSettingDependency::factory()->create([
            'condition' => 'setting.value == "disabled"',
        ]);

        $dependenciesWithCondition = SystemSettingDependency::withCondition('enabled')->get();

        $this->assertTrue($dependenciesWithCondition->contains($dependency1));
        $this->assertFalse($dependenciesWithCondition->contains($dependency2));
    }

    public function test_scope_created_between(): void
    {
        $dependency1 = SystemSettingDependency::factory()->create([
            'created_at' => now()->subDays(5),
        ]);

        $dependency2 = SystemSettingDependency::factory()->create([
            'created_at' => now()->subDays(2),
        ]);

        $dependency3 = SystemSettingDependency::factory()->create([
            'created_at' => now()->subDays(1),
        ]);

        $dependenciesCreatedBetween = SystemSettingDependency::createdBetween(
            now()->subDays(3),
            now()->subDays(1)
        )->get();

        $this->assertFalse($dependenciesCreatedBetween->contains($dependency1));
        $this->assertTrue($dependenciesCreatedBetween->contains($dependency2));
        $this->assertTrue($dependenciesCreatedBetween->contains($dependency3));
    }

    public function test_scope_updated_between(): void
    {
        $dependency1 = SystemSettingDependency::factory()->create([
            'updated_at' => now()->subDays(5),
        ]);

        $dependency2 = SystemSettingDependency::factory()->create([
            'updated_at' => now()->subDays(2),
        ]);

        $dependency3 = SystemSettingDependency::factory()->create([
            'updated_at' => now()->subDays(1),
        ]);

        $dependenciesUpdatedBetween = SystemSettingDependency::updatedBetween(
            now()->subDays(3),
            now()->subDays(1)
        )->get();

        $this->assertFalse($dependenciesUpdatedBetween->contains($dependency1));
        $this->assertTrue($dependenciesUpdatedBetween->contains($dependency2));
        $this->assertTrue($dependenciesUpdatedBetween->contains($dependency3));
    }

    public function test_scope_search(): void
    {
        $setting1 = SystemSetting::factory()->create(['key' => 'setting1']);
        $setting2 = SystemSetting::factory()->create(['key' => 'setting2']);

        $dependency1 = SystemSettingDependency::factory()->create([
            'setting_id' => $setting1->id,
            'condition' => 'setting1.value == "enabled"',
        ]);

        $dependency2 = SystemSettingDependency::factory()->create([
            'setting_id' => $setting2->id,
            'condition' => 'setting2.value == "disabled"',
        ]);

        $searchResults = SystemSettingDependency::search('setting1')->get();

        $this->assertTrue($searchResults->contains($dependency1));
        $this->assertFalse($searchResults->contains($dependency2));
    }

    public function test_scope_order_by_created_at(): void
    {
        $dependency1 = SystemSettingDependency::factory()->create([
            'created_at' => now()->subDays(2),
        ]);

        $dependency2 = SystemSettingDependency::factory()->create([
            'created_at' => now()->subDays(1),
        ]);

        $orderedDependencies = SystemSettingDependency::orderByCreatedAt()->get();

        $this->assertEquals($dependency2->id, $orderedDependencies->first()->id);
        $this->assertEquals($dependency1->id, $orderedDependencies->last()->id);
    }

    public function test_scope_order_by_updated_at(): void
    {
        $dependency1 = SystemSettingDependency::factory()->create([
            'updated_at' => now()->subDays(2),
        ]);

        $dependency2 = SystemSettingDependency::factory()->create([
            'updated_at' => now()->subDays(1),
        ]);

        $orderedDependencies = SystemSettingDependency::orderByUpdatedAt()->get();

        $this->assertEquals($dependency2->id, $orderedDependencies->first()->id);
        $this->assertEquals($dependency1->id, $orderedDependencies->last()->id);
    }

    public function test_scope_order_by_condition(): void
    {
        $dependency1 = SystemSettingDependency::factory()->create([
            'condition' => 'setting.value == "disabled"',
        ]);

        $dependency2 = SystemSettingDependency::factory()->create([
            'condition' => 'setting.value == "enabled"',
        ]);

        $orderedDependencies = SystemSettingDependency::orderByCondition()->get();

        $this->assertEquals($dependency2->id, $orderedDependencies->first()->id);
        $this->assertEquals($dependency1->id, $orderedDependencies->last()->id);
    }

    public function test_scope_order_by_active_status(): void
    {
        $dependency1 = SystemSettingDependency::factory()->create([
            'is_active' => false,
        ]);

        $dependency2 = SystemSettingDependency::factory()->create([
            'is_active' => true,
        ]);

        $orderedDependencies = SystemSettingDependency::orderByActiveStatus()->get();

        $this->assertEquals($dependency2->id, $orderedDependencies->first()->id);
        $this->assertEquals($dependency1->id, $orderedDependencies->last()->id);
    }
}
