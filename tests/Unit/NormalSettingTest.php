<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\NormalSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NormalSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_set_value_persists_string_type(): void
    {
        config(['app.locale' => 'en']);

        NormalSetting::setValue('unit_string', 'value', 'general');

        $setting = NormalSetting::query()->where('key', 'unit_string')->firstOrFail();

        $this->assertSame(NormalSetting::TYPE_STRING, $setting->type);
        $this->assertSame('value', $setting->value);
    }

    public function test_set_value_casts_integer_type(): void
    {
        config(['app.locale' => 'en']);

        NormalSetting::setValue('unit_integer', 42, 'general');

        $setting = NormalSetting::query()->where('key', 'unit_integer')->firstOrFail();

        $this->assertSame(NormalSetting::TYPE_INTEGER, $setting->type);
        $this->assertSame(42, $setting->value);
    }

    public function test_set_value_handles_boolean_type(): void
    {
        config(['app.locale' => 'en']);

        NormalSetting::setValue('unit_boolean', true, 'general');

        $setting = NormalSetting::query()->where('key', 'unit_boolean')->firstOrFail();

        $this->assertSame(NormalSetting::TYPE_BOOLEAN, $setting->type);
        $this->assertTrue($setting->value);
    }

    public function test_set_value_handles_array_type(): void
    {
        config(['app.locale' => 'en']);

        $payload = ['enabled' => true, 'items' => ['a', 'b']];

        NormalSetting::setValue('unit_array', $payload, 'general');

        $setting = NormalSetting::query()->where('key', 'unit_array')->firstOrFail();

        $this->assertSame(NormalSetting::TYPE_ARRAY, $setting->type);
        $this->assertSame($payload, $setting->value);
    }

    public function test_set_value_handles_object_as_json_type(): void
    {
        config(['app.locale' => 'en']);

        $value = (object) ['foo' => 'bar'];

        NormalSetting::setValue('unit_json', $value, 'general');

        $setting = NormalSetting::query()->where('key', 'unit_json')->firstOrFail();

        $this->assertSame(NormalSetting::TYPE_JSON, $setting->type);
        $this->assertSame(['foo' => 'bar'], $setting->value);
    }
}
