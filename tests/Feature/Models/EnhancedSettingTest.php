<?php declare(strict_types=1);

use App\Models\EnhancedSetting;

it('can create and retrieve settings', function () {
    $setting = EnhancedSetting::create([
        'key' => 'test_key',
        'value' => 'test_value',
        'type' => 'text',
        'group' => 'test',
    ]);
    
    expect($setting->getValue())->toBe('test_value');
    expect(EnhancedSetting::get('test_key'))->toBe('test_value');
});

it('handles boolean settings correctly', function () {
    $setting = EnhancedSetting::create([
        'key' => 'test_boolean',
        'value' => true,
        'type' => 'boolean',
    ]);
    
    expect($setting->getValue())->toBe(true);
    expect(EnhancedSetting::get('test_boolean'))->toBe(true);
});

it('handles encrypted settings', function () {
    $setting = EnhancedSetting::create([
        'key' => 'secret_key',
        'value' => 'secret_value',
        'type' => 'text',
        'is_encrypted' => true,
    ]);
    
    expect($setting->getValue())->toBe('secret_value');
    expect(EnhancedSetting::get('secret_key'))->toBe('secret_value');
});

it('returns default value for non-existent setting', function () {
    expect(EnhancedSetting::get('non_existent', 'default'))->toBe('default');
});

it('can update existing settings', function () {
    EnhancedSetting::set('update_test', 'original_value');
    EnhancedSetting::set('update_test', 'updated_value');
    
    expect(EnhancedSetting::get('update_test'))->toBe('updated_value');
});
