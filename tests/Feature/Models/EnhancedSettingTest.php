<?php declare(strict_types=1);

use App\Models\EnhancedSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create and retrieve settings', function () {
    $setting = EnhancedSetting::create([
        'key' => 'test_key',
        'value' => 'test_value',
        'type' => 'text',
        'group' => 'test',
    ]);

    expect($setting->value)->toBe('test_value');
    expect(EnhancedSetting::getValue('test_key'))->toBe('test_value');
});

it('handles boolean settings correctly', function () {
    $setting = EnhancedSetting::create([
        'key' => 'test_boolean',
        'value' => true,
        'type' => 'boolean',
    ]);

    expect($setting->value)->toBe(true);
    expect(EnhancedSetting::getValue('test_boolean'))->toBe(true);
});

it('handles encrypted settings', function () {
    $setting = EnhancedSetting::create([
        'key' => 'secret_key',
        'value' => 'secret_value',
        'type' => 'text',
        'is_encrypted' => true,
    ]);

    expect($setting->value)->toBe('secret_value');
    expect(EnhancedSetting::getValue('secret_key'))->toBe('secret_value');
});

it('returns default value for non-existent setting', function () {
    expect(EnhancedSetting::getValue('non_existent', 'default'))->toBe('default');
});

it('can update existing settings', function () {
    EnhancedSetting::setValue('update_test', 'original_value');
    EnhancedSetting::setValue('update_test', 'updated_value');

    expect(EnhancedSetting::getValue('update_test'))->toBe('updated_value');
});
