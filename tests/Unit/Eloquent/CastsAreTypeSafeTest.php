<?php

declare(strict_types=1);

namespace Tests\Unit\Eloquent;

use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CastsAreTypeSafeTest extends TestCase
{
    use RefreshDatabase;

    public function test_boolean_cast_round_trips_null_and_string_edges(): void
    {
        // Start with a persisted boolean setting, then set value to null via mutator.
        $settingNull = SystemSetting::factory()->ofType('boolean')->create();
        $settingNull->value = null;
        $settingNull->save();
        $this->assertFalse($settingNull->fresh()->value);

        // String edges should normalize correctly.
        $settingZero = SystemSetting::factory()->ofType('boolean')->create(['value' => '0']);
        $this->assertFalse($settingZero->fresh()->value);

        $settingOne = SystemSetting::factory()->ofType('boolean')->create(['value' => '1']);
        $this->assertTrue($settingOne->fresh()->value);

        $settingTrue = SystemSetting::factory()->ofType('boolean')->create(['value' => 'true']);
        $this->assertTrue($settingTrue->fresh()->value);

        $settingFalse = SystemSetting::factory()->ofType('boolean')->create(['value' => 'false']);
        $this->assertFalse($settingFalse->fresh()->value);
    }

    public function test_integer_cast_round_trips_null_and_numeric_edges(): void
    {
        $nullInt = SystemSetting::factory()->ofType('integer')->create();
        $nullInt->value = null; // via mutator -> stored as null
        $nullInt->save();
        $this->assertNull($nullInt->fresh()->value);

        $zeroInt = SystemSetting::factory()->ofType('integer')->create(['value' => 0]);
        $this->assertSame(0, $zeroInt->fresh()->value);

        $stringInt = SystemSetting::factory()->ofType('integer')->create(['value' => '42']);
        $this->assertSame(42, $stringInt->fresh()->value);
    }

    public function test_float_cast_round_trips_null_and_numeric_edges(): void
    {
        $nullFloat = SystemSetting::factory()->ofType('float')->create();
        $nullFloat->value = null; // via mutator -> stored as null
        $nullFloat->save();
        $this->assertNull($nullFloat->fresh()->value);

        $zeroFloat = SystemSetting::factory()->ofType('float')->create(['value' => 0.0]);
        $this->assertSame(0.0, $zeroFloat->fresh()->value);

        $stringFloat = SystemSetting::factory()->ofType('float')->create(['value' => '3.14']);
        $this->assertSame(3.14, $stringFloat->fresh()->value);
    }

    public function test_array_cast_round_trips_null_and_array_edges(): void
    {
        // Null coerces to an empty array on read.
        $nullArray = SystemSetting::factory()->ofType('array')->create();
        $nullArray->value = null;
        $nullArray->save();
        $this->assertSame([], $nullArray->fresh()->value);

        // Native array persists as JSON and returns as array.
        $nativeArray = SystemSetting::factory()->ofType('array')->create();
        $nativeArray->value = ['x', 'y'];
        $nativeArray->save();
        $this->assertSame(['x', 'y'], $nativeArray->fresh()->value);
    }

    public function test_json_cast_round_trips_null_and_array_values(): void
    {
        // Null coerces to an empty array on read.
        $nullJson = SystemSetting::factory()->ofType('json')->create();
        $nullJson->value = null;
        $nullJson->save();
        $this->assertSame([], $nullJson->fresh()->value);

        // Array input persists as JSON and returns as array.
        $goodJson = SystemSetting::factory()->ofType('json')->create();
        $goodJson->value = ['k' => 'v'];
        $goodJson->save();
        $this->assertSame(['k' => 'v'], $goodJson->fresh()->value);
    }

    public function test_string_cast_round_trips_null_and_empty_string(): void
    {
        $nullString = SystemSetting::factory()->ofType('string')->create();
        $nullString->value = null;
        $nullString->save();
        $this->assertNull($nullString->fresh()->value);

        $emptyString = SystemSetting::factory()->ofType('string')->create(['value' => '']);
        $this->assertSame('', (string) $emptyString->fresh()->value);
    }
}
