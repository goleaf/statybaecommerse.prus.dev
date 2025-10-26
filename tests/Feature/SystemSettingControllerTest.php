<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\SystemSettingController;
use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SystemSettingControllerTest
 *
 * Feature coverage for the public SystemSettingController endpoints to ensure
 * filtering and payload normalization behave correctly.
 */
final class SystemSettingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_filters_invalid_related_settings(): void
    {
        // Create a shared category so all generated settings live under the same context.
        $category = SystemSettingCategory::factory()->create([
            'is_active' => true,
            'slug'      => 'display',
        ]);

        $setting = SystemSetting::factory()->create([
            'category_id' => $category->id,
            'key'         => 'display.primary',
            'group'       => 'display',
            'name'        => 'Display Primary',
            'value'       => 'primary-value',
            'is_public'   => true,
            'is_active'   => true,
        ]);
        $setting->category()->associate($category);
        $setting->save();

        $validRelated = SystemSetting::factory()->create([
            'category_id' => $category->id,
            'key'         => 'display.secondary',
            'group'       => 'display',
            'name'        => 'Display Secondary',
            'value'       => 'secondary-value',
            'is_public'   => true,
            'is_active'   => true,
        ]);
        $validRelated->category()->associate($category);
        $validRelated->save();

        // Create a related record that is technically active/public but missing a required name.
        $invalid = SystemSetting::factory()->create([
            'category_id' => $category->id,
            'key'         => 'display.invalid',
            'group'       => 'display',
            'name'        => '',
            'value'       => 'invalid-value',
            'is_public'   => true,
            'is_active'   => true,
        ]);
        $invalid->category()->associate($category);
        $invalid->save();

        $view = app(SystemSettingController::class)->show($setting->key);
        $viewData = $view->getData();

        $relatedSettings = $viewData['relatedSettings'];

        // Only the well-formed related setting should be present in the collection.
        $this->assertSame(1, $relatedSettings->count());
        $this->assertTrue($relatedSettings->first()->is($validRelated));
    }

    public function test_api_trims_and_deduplicates_requested_keys(): void
    {
        $category = SystemSettingCategory::factory()->create([
            'is_active' => true,
            'slug'      => 'api',
        ]);

        $first = SystemSetting::factory()->create([
            'category_id' => $category->id,
            'key'         => 'api.first',
            'group'       => 'api',
            'name'        => 'API First',
            'value'       => 'first-value',
            'is_public'   => true,
            'is_active'   => true,
        ]);
        $first->category()->associate($category);
        $first->save();

        $second = SystemSetting::factory()->create([
            'category_id' => $category->id,
            'key'         => 'api.second',
            'group'       => 'api',
            'name'        => 'API Second',
            'value'       => 'second-value',
            'is_public'   => true,
            'is_active'   => true,
        ]);
        $second->category()->associate($category);
        $second->save();

        $third = SystemSetting::factory()->create([
            'category_id' => $category->id,
            'key'         => 'api.third',
            'group'       => 'api',
            'name'        => 'API Third',
            'value'       => 'third-value',
            'is_public'   => true,
            'is_active'   => true,
        ]);
        $third->category()->associate($category);
        $third->save();

        $response = $this->getJson(route('api.system-settings.index', [
            'keys' => ' api.second , api.first , api.second ,  ',
        ]));

        $response->assertOk();
        $response->assertJson(['api.second' => $second->value, 'api.first' => $first->value]);
        $this->assertCount(2, $response->json());
    }

    public function test_groups_api_excludes_empty_or_blank_groups(): void
    {
        $category = SystemSettingCategory::factory()->create([
            'is_active' => true,
            'slug'      => 'groups',
        ]);

        $validGroupSetting = SystemSetting::factory()->create([
            'category_id' => $category->id,
            'key'         => 'groups.valid',
            'group'       => 'usable',
            'name'        => 'Usable Group',
            'value'       => 'usable-value',
            'is_public'   => true,
            'is_active'   => true,
        ]);
        $validGroupSetting->category()->associate($category);
        $validGroupSetting->save();

        // Insert records that should be ignored by the API because their group field is blank after trimming.
        $emptyGroupSetting = SystemSetting::factory()->create([
            'category_id' => $category->id,
            'key'         => 'groups.empty',
            'group'       => '',
            'name'        => 'Empty Group',
            'value'       => 'empty-value',
            'is_public'   => true,
            'is_active'   => true,
        ]);
        $emptyGroupSetting->category()->associate($category);
        $emptyGroupSetting->save();

        $whitespaceGroupSetting = SystemSetting::factory()->create([
            'category_id' => $category->id,
            'key'         => 'groups.whitespace',
            'group'       => '   ',
            'name'        => 'Whitespace Group',
            'value'       => 'whitespace-value',
            'is_public'   => true,
            'is_active'   => true,
        ]);
        $whitespaceGroupSetting->category()->associate($category);
        $whitespaceGroupSetting->save();

        $response = $this->getJson(route('api.system-settings.groups'));

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJson([['name' => 'usable', 'label' => 'Usable', 'settings_count' => 1]]);
    }
}
