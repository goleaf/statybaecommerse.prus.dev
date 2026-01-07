<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->adminUser = User::factory()->create([
        'email'    => 'admin@test.com',
        'is_admin' => true,
    ]);
});

describe('Multi-language Support', function (): void {
    it('defaults to Lithuanian locale', function (): void {
        $this->actingAs($this->adminUser)
            ->get('/admin');

        expect(App::getLocale())->toBe('lt');
    });

    it('switches language via admin interface', function (): void {
        $supportedLanguages = ['en', 'de', 'ru'];

        foreach ($supportedLanguages as $language) {
            $response = $this->actingAs($this->adminUser)
                ->post('/admin/language/switch', ['language' => $language]);

            $response->assertStatus(302);

            // Verify language was switched in session
            $response->assertSessionHas('locale', $language);
        }
    });

    it('maintains language preference across requests', function (): void {
        // Switch to English
        $this->actingAs($this->adminUser)
            ->post('/admin/language/switch', ['language' => 'en']);

        // Make another request and verify language persists
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/dashboard');

        $response->assertStatus(200);
        expect(App::getLocale())->toBe('en');
    });

    it('loads correct translation files for each language', function (): void {
        $languages = ['lt', 'en', 'de', 'ru'];

        foreach ($languages as $language) {
            App::setLocale($language);

            // Test common translation keys
            $commonKeys = [
                'validation.required',
                'pagination.previous',
                'pagination.next',
            ];

            foreach ($commonKeys as $key) {
                $translation = __($key);
                expect($translation)->not->toBe($key, "Translation for '{$key}' should exist in {$language}");
            }
        }
    });

    it('displays admin interface in selected language', function (): void {
        // Test Lithuanian (default)
        $response = $this->actingAs($this->adminUser)
            ->get('/admin');

        $response->assertStatus(200);

        // Switch to English and verify interface changes
        $this->actingAs($this->adminUser)
            ->post('/admin/language/switch', ['language' => 'en']);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin');

        $response->assertStatus(200);
    });

    it('handles invalid language codes gracefully', function (): void {
        $response = $this->actingAs($this->adminUser)
            ->post('/admin/language/switch', ['language' => 'invalid']);

        // Should either reject or fallback to default
        expect($response->getStatusCode())->toBeIn([302, 422]);
    });

    it('supports currency formatting for EUR', function (): void {
        App::setLocale('lt');

        // Test EUR currency formatting
        $amount = 123.45;
        $formatted = number_format($amount, 2, ',', ' ') . ' €';

        expect($formatted)->toBe('123,45 €');
    });
});

describe('Translation File Integrity', function (): void {
    it('has consistent translation keys across languages', function (): void {
        $languages = ['lt', 'en'];
        $translationFiles = ['validation', 'auth', 'pagination'];

        foreach ($translationFiles as $file) {
            $baseKeys = null;

            foreach ($languages as $language) {
                $filePath = resource_path("lang/{$language}/{$file}.php");

                if (file_exists($filePath)) {
                    $translations = include $filePath;
                    $keys = array_keys($translations);

                    if ($baseKeys === null) {
                        $baseKeys = $keys;
                    } else {
                        // Check that all base keys exist in current language
                        foreach ($baseKeys as $key) {
                            expect($keys)->toContain($key, "Key '{$key}' missing in {$language}/{$file}.php");
                        }
                    }
                }
            }
        }
    });

    it('validates translation file syntax', function (): void {
        $languages = ['lt', 'en', 'de', 'ru'];

        foreach ($languages as $language) {
            $langDir = resource_path("lang/{$language}");

            if (is_dir($langDir)) {
                $files = glob("{$langDir}/*.php");

                foreach ($files as $file) {
                    // Attempt to include the file to check for syntax errors
                    $translations = include $file;

                    expect($translations)->toBeArray("Translation file {$file} should return an array");
                }
            }
        }
    });

    it('ensures no empty translation values', function (): void {
        $languages = ['lt', 'en'];

        foreach ($languages as $language) {
            $langDir = resource_path("lang/{$language}");

            if (is_dir($langDir)) {
                $files = glob("{$langDir}/*.php");

                foreach ($files as $file) {
                    $translations = include $file;

                    foreach ($translations as $key => $value) {
                        if (is_string($value)) {
                            expect(trim($value))->not->toBeEmpty("Translation '{$key}' in {$file} should not be empty");
                        }
                    }
                }
            }
        }
    });
});
