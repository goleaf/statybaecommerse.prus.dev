<?php

declare(strict_types=1);

use App\Enums\LegalDocumentType;
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
it('uses enum for document types', function (): void {
    // Arrange: get types from the model
    $types = Legal::getTypes();

    // Assert: verify the types match the enum options
    expect($types)->toBe(LegalDocumentType::getOptions());
    expect($types)->toHaveKey('privacy_policy');
    expect($types)->toHaveKey('terms_of_use');
});

it('identifies required document types using enum', function (): void {
    // Arrange: get required types from the model
    $requiredTypes = Legal::getRequiredTypes();

    // Assert: verify required types match enum
    expect($requiredTypes)->toContain('privacy_policy');
    expect($requiredTypes)->toContain('terms_of_use');
    expect($requiredTypes)->not->toContain('refund_policy');
});

it('provides status accessor based on enabled and published state', function (): void {
    // Arrange: create documents in different states
    $disabled = Legal::factory()->create(['is_enabled' => false]);
    $draft = Legal::factory()->create(['is_enabled' => true, 'published_at' => null]);
    $published = Legal::factory()->create(['is_enabled' => true, 'published_at' => now()->subDay()]);

    // Assert: verify status calculation
    expect($disabled->status)->toBe('disabled');
    expect($draft->status)->toBe('draft');
    expect($published->status)->toBe('published');
});

it('provides is_published accessor', function (): void {
    // Arrange: create documents with different publish states
    $unpublished = Legal::factory()->create(['published_at' => null]);
    $futurePublished = Legal::factory()->create(['published_at' => now()->addDay()]);
    $published = Legal::factory()->create(['published_at' => now()->subDay()]);

    // Assert: verify publication status
    expect($unpublished->is_published)->toBeFalse();
    expect($futurePublished->is_published)->toBeFalse();
    expect($published->is_published)->toBeTrue();
});

it('provides helper methods for document state management', function (): void {
    // Arrange: create a legal document
    $legal = Legal::factory()->create([
        'is_enabled'   => false,
        'is_required'  => false,
        'published_at' => null,
    ]);

    // Act & Assert: test enable/disable
    expect($legal->enable())->toBeTrue();
    expect($legal->fresh()->is_enabled)->toBeTrue();

    expect($legal->disable())->toBeTrue();
    expect($legal->fresh()->is_enabled)->toBeFalse();

    // Act & Assert: test publish/unpublish
    expect($legal->publish())->toBeTrue();
    expect($legal->fresh()->published_at)->not->toBeNull();

    expect($legal->unpublish())->toBeTrue();
    expect($legal->fresh()->published_at)->toBeNull();

    // Act & Assert: test required/optional
    expect($legal->makeRequired())->toBeTrue();
    expect($legal->fresh()->is_required)->toBeTrue();

    expect($legal->makeOptional())->toBeTrue();
    expect($legal->fresh()->is_required)->toBeFalse();
});

it('provides static methods for retrieving documents', function (): void {
    // Arrange: create test documents
    $published = Legal::factory()->enabled()->published()->create(['key' => 'test-doc']);
    $disabled = Legal::factory()->disabled()->create(['key' => 'disabled-doc']);
    $required = Legal::factory()->enabled()->published()->required()->create(['type' => 'privacy_policy']);

    // Act & Assert: test getByKey
    $found = Legal::getByKey('test-doc');
    expect($found)->not->toBeNull();
    expect($found->id)->toBe($published->id);

    $notFound = Legal::getByKey('disabled-doc'); // disabled documents shouldn't be found
    expect($notFound)->toBeNull();

    // Act & Assert: test getRequiredDocuments
    $requiredDocs = Legal::getRequiredDocuments();
    expect($requiredDocs)->toHaveCount(1);
    expect($requiredDocs->first()->id)->toBe($required->id);

    // Act & Assert: test getByType
    $privacyDocs = Legal::getByType('privacy_policy');
    expect($privacyDocs)->toHaveCount(1);
    expect($privacyDocs->first()->id)->toBe($required->id);
});

it('handles translation field retrieval with proper null checks', function (): void {
    // Arrange: create a legal document with a translation
    $legal = Legal::factory()->create();
    $translation = \App\Models\Translations\LegalTranslation::factory()->create([
        'legal_id'        => $legal->id,
        'locale'          => 'en',
        'title'           => 'Test Title',
        'content'         => '<p>Test content</p>',
        'slug'            => 'test-slug',
        'seo_title'       => 'SEO Title',
        'seo_description' => 'SEO Description',
    ]);

    // Act & Assert: test translation field retrieval
    expect($legal->getTranslatedTitle('en'))->toBe('Test Title');
    expect($legal->getTranslatedSlug('en'))->toBe('test-slug');
    expect($legal->getTranslatedSeoTitle('en'))->toBe('SEO Title');
    expect($legal->getTranslatedSeoDescription('en'))->toBe('SEO Description');

    // Test with non-existent locale
    expect($legal->getTranslatedTitle('fr'))->toBeNull();
    expect($legal->getTranslatedSlug('fr'))->toBeNull();
});
