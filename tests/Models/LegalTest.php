<?php

declare(strict_types=1);

use App\Models\Legal;
use App\Models\Translations\LegalTranslation;
use App\Services\Security\HtmlContentSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

// Leverage the shared RefreshDatabase trait while the global Pest configuration boots the Laravel TestCase.
uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    // Ensure the framework resolves content translations against a predictable locale.
    // Using the facade keeps the application locale in sync with translation helpers.
    App::setLocale('en');
});

afterEach(function (): void {
    // Reset the sanitizer binding (in case a test overrides it) and close any Mockery state.
    app()->forgetInstance(HtmlContentSanitizer::class);
    \Mockery::close();
});

it('returns sanitized translated content for the requested locale', function (): void {
    // Arrange: create a legal document with an English translation that contains HTML needing sanitation.
    $legal = Legal::factory()->create();
    $originalContent = '<script>alert(1)</script><p>Safe</p>';
    LegalTranslation::factory()->create([
        'legal_id' => $legal->id,
        'locale'   => 'en',
        'content'  => $originalContent,
    ]);

    // Arrange: resolve the sanitizer directly to calculate the expected cleaned output.
    $expectedSanitized = app(HtmlContentSanitizer::class)->sanitize($originalContent);

    // Act & Assert: the sanitized value should be returned when requesting the translated content.
    expect($expectedSanitized)->not->toBe($originalContent);
    expect($legal->fresh()->getTranslatedContent('en'))->toBe($expectedSanitized);
});

it('returns null when a translation is missing for the requested locale', function (): void {
    // Arrange: create a legal record without any translation assigned.
    $legal = Legal::factory()->create();

    // Act & Assert: the helper should gracefully return null.
    expect($legal->getTranslatedContent('en'))->toBeNull();
});

it('exposes available locales without duplicates or empty values', function (): void {
    // Arrange: seed two translations with different locales for the same legal record.
    $legal = Legal::factory()->create();
    LegalTranslation::factory()->english()->create(['legal_id' => $legal->id]);
    LegalTranslation::factory()->lithuanian()->create(['legal_id' => $legal->id]);

    // Act & Assert: ensure the locale list contains both locales without any extraneous entries.
    expect($legal->fresh()->getAvailableLocales())->toEqualCanonicalizing(['en', 'lt']);
});

it('detects existing translations and can create defaults when missing', function (): void {
    // Arrange: create a base legal document with a deterministic key value.
    $legal = Legal::factory()->create(['key' => 'privacy-policy']);

    // Act: create a translation on demand for a locale that does not yet exist.
    $createdTranslation = $legal->getOrCreateTranslation('lt');

    // Assert: the translation exists with sensible default values derived from the document key.
    expect($createdTranslation->exists)->toBeTrue();
    expect($createdTranslation->title)->toBe('privacy-policy');
    expect($createdTranslation->slug)->toBe(Str::slug('privacy-policy') . '-lt');
    expect($legal->hasTranslationFor('lt'))->toBeTrue();
});

it('updates an existing translation through the helper', function (): void {
    // Arrange: seed an English translation and capture its instance.
    $legal = Legal::factory()->create();
    $translation = LegalTranslation::factory()->english()->create([
        'legal_id' => $legal->id,
        'title'    => 'Original title',
    ]);

    // Act: update the translation payload using the dedicated helper method.
    $legal->updateTranslation('en', ['title' => 'Updated title']);

    // Assert: verify the persisted value matches the expected new title.
    expect($translation->fresh()->title)->toBe('Updated title');
});
