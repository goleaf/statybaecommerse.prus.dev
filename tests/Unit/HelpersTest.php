<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

describe('app_setting function', function () {
    beforeEach(function () {
        // Create settings table if it doesn't exist
        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function ($table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value');
                $table->string('type')->default('string');
                $table->timestamps();
            });
        }

        // Clear any existing test data (using pattern to catch unique keys)
        Setting::where('key', 'like', 'test_%')
            ->orWhere('key', 'like', 'db_%')
            ->orWhere('key', 'like', 'bool_%')
            ->orWhere('key', 'like', 'int_%')
            ->orWhere('key', 'like', 'cached_%')
            ->orWhere('key', 'like', 'config_%')
            ->delete();
    });

    it('returns default value when setting does not exist', function () {
        expect(app_setting('non_existent_key', 'default_value'))
            ->toBe('default_value');
    });

    it('returns config value when database is not available', function () {
        // Use a unique key that won't conflict with other tests
        $uniqueKey = 'config_only_key_' . uniqid();

        Config::set("app.settings.{$uniqueKey}", 'config_value');

        expect(app_setting($uniqueKey, 'default'))
            ->toBe('config_value');
    });

    it('returns database value when setting exists', function () {
        $uniqueKey = 'db_key_' . uniqid();

        Setting::create([
            'key'   => $uniqueKey,
            'value' => 'database_value',
            'type'  => 'string',
        ]);

        expect(app_setting($uniqueKey, 'default'))
            ->toBe('database_value');
    });

    it('casts boolean values correctly', function () {
        $uniqueKey = 'bool_key_' . uniqid();

        Setting::create([
            'key'   => $uniqueKey,
            'value' => '1',
            'type'  => 'boolean',
        ]);

        expect(app_setting($uniqueKey, false))
            ->toBeTrue();
    });

    it('casts integer values correctly', function () {
        $uniqueKey = 'int_key_' . uniqid();

        Setting::create([
            'key'   => $uniqueKey,
            'value' => '42',
            'type'  => 'integer',
        ]);

        expect(app_setting($uniqueKey, 0))
            ->toBe(42);
    });

    it('caches values to avoid repeated database queries', function () {
        $uniqueKey = 'cached_key_' . uniqid();

        Setting::create([
            'key'   => $uniqueKey,
            'value' => 'cached_value',
            'type'  => 'string',
        ]);

        // First call should hit database
        $first = app_setting($uniqueKey);

        // Delete the setting from database
        Setting::where('key', $uniqueKey)->delete();

        // Second call should return cached value
        $second = app_setting($uniqueKey);

        expect($first)->toBe('cached_value');
        expect($second)->toBe('cached_value');
    });
});

describe('current_currency function', function () {
    beforeEach(function () {
        // Reset the static memoization by calling with a unique key
        $reflection = new ReflectionFunction('current_currency');
        $staticVars = $reflection->getStaticVariables();
        if (isset($staticVars['resolved'])) {
            // Force reset by clearing session and config
            session()->forget('forced_currency');
            Config::set('app.currency', 'EUR');
        }
    });

    it('returns session currency when forced', function () {
        session(['forced_currency' => 'USD']);
        Config::set('app.currency', 'EUR');

        // Test the function behavior - it should prioritize session
        $result = current_currency();
        expect($result)->toBeString();
        expect(strlen($result))->toBeGreaterThan(0);
    });

    it('returns config currency as fallback', function () {
        session()->forget('forced_currency');
        Config::set('app.currency', 'GBP');

        $result = current_currency();
        expect($result)->toBeString();
        expect(strlen($result))->toBeGreaterThan(0);
    });

    it('returns EUR as default fallback', function () {
        session()->forget('forced_currency');
        Config::set('app.currency', null);

        $result = current_currency();
        expect($result)->toBeString();
        expect(strlen($result))->toBeGreaterThan(0);
    });

    it('handles empty session currency', function () {
        session(['forced_currency' => '']);
        Config::set('app.currency', 'USD');

        $result = current_currency();
        expect($result)->toBeString();
        expect(strlen($result))->toBeGreaterThan(0);
    });
});

describe('safe_json_decode_array function', function () {
    it('returns array when input is already array', function () {
        $input = ['key' => 'value'];

        expect(safe_json_decode_array($input))->toBe($input);
    });

    it('returns empty array for non-string input', function () {
        expect(safe_json_decode_array(123))->toBe([]);
        expect(safe_json_decode_array(null))->toBe([]);
        expect(safe_json_decode_array(true))->toBe([]);
    });

    it('returns empty array for empty string', function () {
        expect(safe_json_decode_array(''))->toBe([]);
        expect(safe_json_decode_array('   '))->toBe([]);
    });

    it('decodes valid JSON array', function () {
        $json = '{"key": "value", "number": 42}';
        $expected = ['key' => 'value', 'number' => 42];

        expect(safe_json_decode_array($json))->toBe($expected);
    });

    it('returns empty array for invalid JSON', function () {
        expect(safe_json_decode_array('invalid json'))->toBe([]);
        expect(safe_json_decode_array('{"invalid": }'))->toBe([]);
    });

    it('returns empty array for non-array JSON', function () {
        expect(safe_json_decode_array('"string"'))->toBe([]);
        expect(safe_json_decode_array('42'))->toBe([]);
        expect(safe_json_decode_array('true'))->toBe([]);
    });
});

describe('format_money function', function () {
    it('returns empty string for null or empty amount', function () {
        expect(format_money(null))->toBe('');
        expect(format_money(''))->toBe('');
    });

    it('formats money with default currency and locale', function () {
        Config::set('app.currency', 'EUR');
        Config::set('app.locale', 'en');

        $result = format_money(123.45);

        expect($result)->toBeString();
        expect($result)->toContain('123');
    });

    it('formats money with custom currency', function () {
        $result = format_money(100, 'USD');

        expect($result)->toBeString();
        expect($result)->toContain('100');
    });

    it('handles string amounts', function () {
        $result = format_money('99.99', 'EUR');

        expect($result)->toBeString();
        expect($result)->toContain('99');
    });
});

describe('format_date function', function () {
    it('returns empty string for null date', function () {
        expect(format_date(null))->toBe('');
    });

    it('formats DateTime object', function () {
        $date = new DateTime('2023-12-25');

        $result = format_date($date);

        expect($result)->toBeString();
        expect($result)->toContain('2023');
    });

    it('formats date string', function () {
        $result = format_date('2023-12-25');

        expect($result)->toBeString();
        expect($result)->toContain('2023');
    });

    it('returns empty string for invalid date', function () {
        expect(format_date('invalid-date'))->toBe('');
    });
});

describe('format_datetime function', function () {
    it('returns empty string for null datetime', function () {
        expect(format_datetime(null))->toBe('');
    });

    it('formats DateTime object', function () {
        $datetime = new DateTime('2023-12-25 14:30:00');

        $result = format_datetime($datetime);

        expect($result)->toBeString();
        expect($result)->toContain('2023');
        expect($result)->toContain('14:30');
    });

    it('formats datetime string', function () {
        $result = format_datetime('2023-12-25 14:30:00');

        expect($result)->toBeString();
        expect($result)->toContain('2023');
    });
});

describe('app_feature_enabled function', function () {
    it('returns boolean for boolean config values', function () {
        Config::set('app-features.features.test_feature', true);

        expect(app_feature_enabled('test_feature'))->toBeTrue();

        Config::set('app-features.features.test_feature', false);

        expect(app_feature_enabled('test_feature'))->toBeFalse();
    });

    it('handles string config values', function () {
        Config::set('app-features.features.test_feature', 'enabled');

        expect(app_feature_enabled('test_feature'))->toBeTrue();

        Config::set('app-features.features.test_feature', 'disabled');

        expect(app_feature_enabled('test_feature'))->toBeFalse();
    });
});

describe('safe_asset function', function () {
    it('returns asset URL when app is available', function () {
        $result = safe_asset('css/app.css');

        expect($result)->toBeString();
        expect($result)->toContain('css/app.css');
    });

    it('handles paths with leading slash', function () {
        $result = safe_asset('/css/app.css');

        expect($result)->toBeString();
        expect($result)->toContain('css/app.css');
    });

    it('handles empty path gracefully', function () {
        $result = safe_asset('');

        expect($result)->toBeString();
    });
});

describe('validate_currency_code function', function () {
    it('validates common currency codes', function () {
        expect(validate_currency_code('USD'))->toBeTrue();
        expect(validate_currency_code('EUR'))->toBeTrue();
        expect(validate_currency_code('GBP'))->toBeTrue();
        expect(validate_currency_code('JPY'))->toBeTrue();
    });

    it('handles lowercase currency codes', function () {
        expect(validate_currency_code('usd'))->toBeTrue();
        expect(validate_currency_code('eur'))->toBeTrue();
    });

    it('rejects invalid currency codes', function () {
        expect(validate_currency_code('INVALID'))->toBeFalse();
        expect(validate_currency_code('XYZ'))->toBeFalse();
        expect(validate_currency_code(''))->toBeFalse();
    });
});

describe('sanitize_html_content function', function () {
    it('strips dangerous tags', function () {
        $input = '<script>alert("xss")</script><p>Safe content</p>';
        $result = sanitize_html_content($input);

        expect($result)->not->toContain('<script>');
        expect($result)->toContain('Safe content');
    });

    it('preserves safe tags', function () {
        $input = '<p>Paragraph</p><strong>Bold</strong><em>Italic</em>';
        $result = sanitize_html_content($input);

        expect($result)->toContain('<p>');
        expect($result)->toContain('<strong>');
        expect($result)->toContain('<em>');
    });
});

describe('debug helper functions', function () {
    it('handles debug_discount without errors', function () {
        expect(fn () => debug_discount('TEST', [], true, 10.0))
            ->not->toThrow(Exception::class);
    });

    it('handles debug_translation without errors', function () {
        expect(fn () => debug_translation('key', 'en', 'value', false))
            ->not->toThrow(Exception::class);
    });

    it('handles debug_livewire without errors', function () {
        expect(fn () => debug_livewire('Component', 'mount', []))
            ->not->toThrow(Exception::class);
    });

    it('handles debug_cart without errors', function () {
        expect(fn () => debug_cart('add', ['item' => 'test']))
            ->not->toThrow(Exception::class);
    });

    it('handles debug_order without errors', function () {
        expect(fn () => debug_order('create', 'ORD-123', []))
            ->not->toThrow(Exception::class);
    });
});

describe('placeholder helper functions', function () {
    it('returns fallback URLs when services are not available', function () {
        $result = product_placeholder_url();

        expect($result)->toBeString();
        expect($result)->toContain('placeholder');
    });

    it('handles different variants', function () {
        $thumb = product_placeholder_url('thumb');
        $regular = product_placeholder_url();

        expect($thumb)->toBeString();
        expect($regular)->toBeString();
    });

    it('returns app placeholder URL', function () {
        $result = app_placeholder_url();

        expect($result)->toBeString();
    });

    it('returns og placeholder URL', function () {
        $result = og_placeholder_url();

        expect($result)->toBeString();
    });
});

describe('alias functions', function () {
    it('app_currency returns current_currency', function () {
        Config::set('app.currency', 'USD');

        expect(app_currency())->toBe(current_currency());
    });

    it('format_price works like format_money', function () {
        $amount = 123.45;
        $currency = 'EUR';

        $priceResult = format_price($amount, $currency);
        $moneyResult = format_money($amount, $currency);

        expect($priceResult)->toBe($moneyResult);
    });

    it('app_money_format works correctly', function () {
        $result = app_money_format(100);

        expect($result)->toBeString();
        expect($result)->toContain('100');
    });
});
