<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

describe('app_setting helper', function () {
    it('returns config value when setting exists', function () {
        config(['app.settings.test_key_1' => 'config_value']);

        expect(app_setting('test_key_1'))->toBe('config_value');
    });

    it('returns default value when setting does not exist', function () {
        expect(app_setting('non_existent_key_2', 'default_value'))
            ->toBe('default_value');
    });

    it('returns null when no default provided and setting does not exist', function () {
        expect(app_setting('non_existent_key_3'))->toBeNull();
    });

    it('handles nested config keys correctly', function () {
        config(['app.settings.nested.key_4' => 'nested_value']);

        expect(app_setting('nested.key_4'))->toBe('nested_value');
    });

    it('handles different data types from config', function () {
        config([
            'app.settings.string_value_5'  => 'test_string',
            'app.settings.integer_value_5' => 42,
            'app.settings.boolean_value_5' => true,
            'app.settings.array_value_5'   => ['key' => 'value'],
            'app.settings.float_value_5'   => 3.14,
        ]);

        expect(app_setting('string_value_5'))->toBe('test_string')
            ->and(app_setting('integer_value_5'))->toBe(42)
            ->and(app_setting('boolean_value_5'))->toBeTrue()
            ->and(app_setting('array_value_5'))->toBe(['key' => 'value'])
            ->and(app_setting('float_value_5'))->toBe(3.14);
    });

    it('prefers config over default when config exists', function () {
        config(['app.settings.existing_key_6' => 'config_value']);

        expect(app_setting('existing_key_6', 'default_value'))
            ->toBe('config_value');
    });

    it('prefers database setting over config when available', function () {
        config(['app.settings.db_test_key' => 'config_value']);

        Setting::factory()->create([
            'key'   => 'db_test_key',
            'value' => 'database_value',
            'type'  => 'string',
        ]);

        expect(app_setting('db_test_key'))->toBe('database_value');
    });

    it('handles database settings with type casting', function () {
        Setting::factory()->create([
            'key'   => 'boolean_setting',
            'value' => 'true',
            'type'  => 'boolean',
        ]);

        Setting::factory()->create([
            'key'   => 'integer_setting',
            'value' => '42',
            'type'  => 'integer',
        ]);

        Setting::factory()->create([
            'key'   => 'float_setting',
            'value' => '3.14',
            'type'  => 'float',
        ]);

        Setting::factory()->create([
            'key'   => 'json_setting',
            'value' => '{"key": "value"}',
            'type'  => 'json',
        ]);

        expect(app_setting('boolean_setting'))->toBeTrue()
            ->and(app_setting('integer_setting'))->toBe(42)
            ->and(app_setting('float_setting'))->toBe(3.14)
            ->and(app_setting('json_setting'))->toBe(['key' => 'value']);
    });

    it('uses static caching to avoid repeated database queries', function () {
        Setting::factory()->create([
            'key'   => 'cached_setting',
            'value' => 'cached_value',
            'type'  => 'string',
        ]);

        // First call should hit database
        $firstCall = app_setting('cached_setting');

        // Delete the setting from database
        Setting::where('key', 'cached_setting')->delete();

        // Second call should return cached value
        $secondCall = app_setting('cached_setting');

        expect($firstCall)->toBe('cached_value')
            ->and($secondCall)->toBe('cached_value');
    });

    it('falls back to config when database query fails', function () {
        config(['app.settings.fallback_key' => 'config_fallback']);

        // Mock Schema to return false for hasTable check
        Schema::shouldReceive('hasTable')
            ->with('settings')
            ->andReturn(false);

        expect(app_setting('fallback_key'))->toBe('config_fallback');
    });

    it('handles database exceptions gracefully', function () {
        config(['app.settings.exception_key' => 'config_value']);

        // This should not throw an exception even if database fails
        expect(fn () => app_setting('exception_key'))->not->toThrow(Exception::class);
    });
});

describe('current_currency helper', function () {
    it('returns a valid currency code', function () {
        $currency = current_currency();

        expect($currency)->toBeString()
            ->and(strlen($currency))->toBeGreaterThan(0);
    });

    it('returns database currency setting when available', function () {
        $setting = Setting::factory()->create([
            'key'   => 'currency_code',
            'value' => 'EUR',
            'type'  => 'string',
        ]);

        // Verify the setting was created
        expect($setting->key)->toBe('currency_code')
            ->and($setting->value)->toBe('EUR');

        // Test that app_setting works correctly for currency
        expect(app_setting('currency_code'))->toBe('EUR');
        expect(current_currency())->toBe('EUR');
    });

    it('uses config fallback when no database setting exists', function () {
        config(['app.currency' => 'USD']);
        expect(current_currency())->toBe('EUR');
    });
});

describe('app_currency helper', function () {
    it('returns same value as current_currency', function () {
        expect(app_currency())->toBe(current_currency());
    });
});

describe('safe_json_decode_array helper', function () {
    it('returns array when given valid JSON string', function () {
        $json = '{"key": "value", "number": 42}';

        expect(safe_json_decode_array($json))
            ->toBe(['key' => 'value', 'number' => 42]);
    });

    it('returns empty array for invalid JSON', function () {
        expect(safe_json_decode_array('invalid json'))
            ->toBe([]);
    });

    it('returns array as-is when given array', function () {
        $array = ['key' => 'value'];

        expect(safe_json_decode_array($array))->toBe($array);
    });

    it('returns empty array for non-string, non-array input', function () {
        expect(safe_json_decode_array(123))->toBe([])
            ->and(safe_json_decode_array(null))->toBe([])
            ->and(safe_json_decode_array(true))->toBe([]);
    });

    it('returns empty array for empty string', function () {
        expect(safe_json_decode_array(''))->toBe([])
            ->and(safe_json_decode_array('   '))->toBe([]);
    });
});

describe('format_money helper', function () {
    it('returns empty string for null or empty amount', function () {
        expect(format_money(null))->toBe('')
            ->and(format_money(''))->toBe('');
    });

    it('formats money with default currency', function () {
        $result = format_money(100.50);

        expect($result)->toContain('100')
            ->and($result)->toContain('50');
    });

    it('formats money with specified currency', function () {
        $result = format_money(100.50, 'EUR');

        expect($result)->toContain('100')
            ->and($result)->toContain('50');
    });

    it('uses fallback formatting when advanced formatters fail', function () {
        // Test the fallback function directly
        $result = formatMoneyFallback(1234.56, 'EUR', 'en');

        expect($result)->toContain('EUR')
            ->and($result)->toContain('1,234.56');
    });
});

describe('format_price helper', function () {
    it('returns empty string for null amount', function () {
        expect(format_price(null))->toBe('');
    });

    it('formats price using format_money', function () {
        $amount = 99.99;
        $currency = 'EUR';

        expect(format_price($amount, $currency))
            ->toBe(format_money($amount, $currency));
    });
});

describe('app_money_format helper', function () {
    it('formats money with current currency', function () {
        $result = app_money_format(50.25);

        expect($result)->toContain('50')
            ->and($result)->toContain('25');
    });
});

describe('date formatting helpers', function () {
    it('format_date returns empty string for null input', function () {
        expect(format_date(null))->toBe('');
    });

    it('format_date formats DateTime objects', function () {
        $date = new DateTime('2023-12-25');

        expect(format_date($date))->toBe('2023-12-25');
    });

    it('format_date formats string dates', function () {
        expect(format_date('2023-12-25'))->toBe('2023-12-25');
    });

    it('format_datetime returns empty string for null input', function () {
        expect(format_datetime(null))->toBe('');
    });

    it('format_datetime formats DateTime objects with time', function () {
        $datetime = new DateTime('2023-12-25 14:30:00');

        expect(format_datetime($datetime))->toBe('2023-12-25 14:30');
    });

    it('handles invalid date strings gracefully', function () {
        expect(format_date('invalid-date'))->toBe('')
            ->and(format_datetime('invalid-datetime'))->toBe('');
    });
});

describe('app_feature_enabled helper', function () {
    it('returns boolean for feature state', function () {
        config(['app-features.features.test_feature' => true]);

        expect(app_feature_enabled('test_feature'))->toBeTrue();
    });

    it('returns false for disabled features', function () {
        config(['app-features.features.disabled_feature' => false]);

        expect(app_feature_enabled('disabled_feature'))->toBeFalse();
    });
});

describe('debug helpers', function () {
    it('debug_discount does not throw exceptions', function () {
        expect(fn () => debug_discount('TEST', [], true, 10.0))
            ->not->toThrow(Exception::class);
    });

    it('debug_translation does not throw exceptions', function () {
        expect(fn () => debug_translation('key', 'en', 'value', false))
            ->not->toThrow(Exception::class);
    });

    it('debug_livewire does not throw exceptions', function () {
        expect(fn () => debug_livewire('Component', 'mount', []))
            ->not->toThrow(Exception::class);
    });

    it('debug_cart does not throw exceptions', function () {
        expect(fn () => debug_cart('add', ['item' => 'test']))
            ->not->toThrow(Exception::class);
    });

    it('debug_order does not throw exceptions', function () {
        expect(fn () => debug_order('create', 'ORD-123', []))
            ->not->toThrow(Exception::class);
    });
});

describe('asset helpers', function () {
    it('safe_asset returns relative path as fallback', function () {
        $result = safe_asset('images/test.jpg');

        expect($result)->toContain('images/test.jpg');
    });

    it('placeholder helpers return valid URLs', function () {
        expect(app_placeholder_url())->toBeString()
            ->and(product_placeholder_url())->toBeString()
            ->and(product_placeholder_url('thumb'))->toBeString()
            ->and(og_placeholder_url())->toBeString();
    });
});

describe('validate_currency_code helper', function () {
    it('validates only eur currency code', function () {
        expect(validate_currency_code('EUR'))->toBeTrue()
            ->and(validate_currency_code('eur'))->toBeTrue();
    });

    it('rejects invalid currency codes', function () {
        expect(validate_currency_code('INVALID'))->toBeFalse()
            ->and(validate_currency_code('ABC'))->toBeFalse()
            ->and(validate_currency_code(''))->toBeFalse()
            ->and(validate_currency_code('US'))->toBeFalse();
    });

    it('handles case insensitive validation', function () {
        expect(validate_currency_code('eur'))->toBeTrue()
            ->and(validate_currency_code('EuR'))->toBeTrue();
    });
});

describe('sanitize_html_content helper', function () {
    it('removes dangerous HTML tags', function () {
        $dangerousHtml = '<script>alert("xss")</script><p>Safe content</p>';
        $result = sanitize_html_content($dangerousHtml);

        expect($result)->not->toContain('<script>')
            ->and($result)->toContain('Safe content');
    });

    it('preserves allowed HTML tags', function () {
        $safeHtml = '<p>Paragraph</p><strong>Bold</strong><em>Italic</em>';
        $result = sanitize_html_content($safeHtml);

        expect($result)->toContain('<p>')
            ->and($result)->toContain('<strong>')
            ->and($result)->toContain('<em>');
    });

    it('handles empty content gracefully', function () {
        expect(sanitize_html_content(''))->toBe('');
    });
});

describe('get_tenant_setting helper', function () {
    it('falls back to app_setting for now', function () {
        config(['app.settings.tenant_test' => 'tenant_value']);

        expect(get_tenant_setting('tenant_test'))->toBe('tenant_value');
    });

    it('returns default when setting does not exist', function () {
        expect(get_tenant_setting('non_existent_tenant_setting', 'default'))
            ->toBe('default');
    });
});

describe('app_setting_flush_cache helper', function () {
    it('does not throw exceptions when called', function () {
        expect(fn () => app_setting_flush_cache())->not->toThrow(Exception::class);
    });
});
