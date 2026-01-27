<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Test suite for categories translation completeness and correctness.
 * 
 * Validates that all category-related translation keys exist in both
 * Lithuanian and English locales, and that they render correctly in UI context.
 */
describe('Categories Translation', function () {
    
    /**
     * Test that newly added UI label translations exist in both locales.
     */
    it('has UI label translations in both locales', function () {
        $keys = ['index_close', 'show_adjust_filters'];
        
        foreach ($keys as $key) {
            // Test Lithuanian translations
            $ltTranslation = __("categories.{$key}", [], 'lt');
            expect($ltTranslation)->not->toBe("categories.{$key}")
                ->and($ltTranslation)->not->toBeEmpty();
            
            // Test English translations
            $enTranslation = __("categories.{$key}", [], 'en');
            expect($enTranslation)->not->toBe("categories.{$key}")
                ->and($enTranslation)->not->toBeEmpty();
        }
    });
    
    /**
     * Test that translations are contextually appropriate.
     */
    it('provides contextually appropriate translations', function () {
        // Test close button translation
        expect(__('categories.index_close', [], 'lt'))->toBe('Uždaryti');
        expect(__('categories.index_close', [], 'en'))->toBe('Close');
        
        // Test filter guidance translation
        expect(__('categories.show_adjust_filters', [], 'lt'))
            ->toBe('Koreguokite filtrus, kad rastumėte tobulus produktus');
        expect(__('categories.show_adjust_filters', [], 'en'))
            ->toBe('Adjust your filters to find the perfect products');
    });
    
    /**
     * Test that all core category field translations exist.
     */
    it('has complete field translations', function () {
        $fields = [
            'name', 'slug', 'description', 'short_description',
            'parent', 'sort_order', 'is_enabled', 'is_visible',
            'is_featured', 'show_in_menu', 'product_limit'
        ];
        
        foreach ($fields as $field) {
            expect(__("categories.fields.{$field}", [], 'lt'))->not->toBe("categories.fields.{$field}");
            expect(__("categories.fields.{$field}", [], 'en'))->not->toBe("categories.fields.{$field}");
        }
    });
    
    /**
     * Test that validation message translations exist.
     */
    it('has validation message translations', function () {
        $validationKeys = [
            'seo_title_max', 'seo_description_max', 'seo_keywords_max',
            'sort_order_numeric', 'product_limit_numeric'
        ];
        
        foreach ($validationKeys as $key) {
            expect(__("categories.validation.{$key}", [], 'lt'))->not->toBe("categories.validation.{$key}");
            expect(__("categories.validation.{$key}", [], 'en'))->not->toBe("categories.validation.{$key}");
        }
    });
    
    /**
     * Test that action translations exist for user interactions.
     */
    it('has action translations', function () {
        $actions = [
            'translate', 'view_products', 'duplicate',
            'enable_selected', 'disable_selected', 'feature_selected'
        ];
        
        foreach ($actions as $action) {
            expect(__("categories.actions.{$action}", [], 'lt'))->not->toBe("categories.actions.{$action}");
            expect(__("categories.actions.{$action}", [], 'en'))->not->toBe("categories.actions.{$action}");
        }
    });
    
    /**
     * Test that message translations exist for system feedback.
     */
    it('has system message translations', function () {
        $messages = [
            'created', 'updated', 'deleted',
            'no_categories_found', 'create_first_category'
        ];
        
        foreach ($messages as $message) {
            expect(__("categories.messages.{$message}", [], 'lt'))->not->toBe("categories.messages.{$message}");
            expect(__("categories.messages.{$message}", [], 'en'))->not->toBe("categories.messages.{$message}");
        }
    });
    
    /**
     * Test translation consistency between locales.
     */
    it('maintains consistent structure between locales', function () {
        $ltCategories = trans('categories', [], 'lt');
        $enCategories = trans('categories', [], 'en');
        
        // Both should be arrays
        expect($ltCategories)->toBeArray();
        expect($enCategories)->toBeArray();
        
        // Check that core sections exist in both
        $coreSections = ['fields', 'actions', 'messages', 'validation'];
        
        foreach ($coreSections as $section) {
            expect($ltCategories)->toHaveKey($section);
            expect($enCategories)->toHaveKey($section);
        }
        
        // Check that new UI labels exist in both
        expect($ltCategories)->toHaveKey('index_close');
        expect($enCategories)->toHaveKey('index_close');
        expect($ltCategories)->toHaveKey('show_adjust_filters');
        expect($enCategories)->toHaveKey('show_adjust_filters');
    });
    
    /**
     * Test that translations don't contain placeholder text.
     */
    it('does not contain placeholder text', function () {
        $keys = ['index_close', 'show_adjust_filters'];
        
        foreach ($keys as $key) {
            $ltTranslation = __("categories.{$key}", [], 'lt');
            $enTranslation = __("categories.{$key}", [], 'en');
            
            // Ensure we have string values
            expect($ltTranslation)->toBeString();
            expect($enTranslation)->toBeString();
            
            // Should not contain common placeholder patterns
            expect($ltTranslation)->not->toContain('TODO');
            expect($ltTranslation)->not->toContain('FIXME');
            expect($ltTranslation)->not->toContain('placeholder');
            expect($ltTranslation)->not->toContain('test');
            
            expect($enTranslation)->not->toContain('TODO');
            expect($enTranslation)->not->toContain('FIXME');
            expect($enTranslation)->not->toContain('placeholder');
            expect($enTranslation)->not->toContain('test');
        }
    });
    
    /**
     * Test that Lithuanian translations contain appropriate characters.
     */
    it('uses proper Lithuanian characters where appropriate', function () {
        $ltTranslations = trans('categories', [], 'lt');
        
        // Flatten the array to check all values
        $allValues = collect($ltTranslations)->flatten()->filter(fn($value) => is_string($value));
        
        // Should contain some Lithuanian-specific characters in the dataset
        $hasLithuanianChars = $allValues->some(function ($value) {
            return preg_match('/[ąčęėįšųūž]/u', $value);
        });
        
        expect($hasLithuanianChars)->toBeTrue();
    });
    
    /**
     * Test that translations are appropriate length for UI elements.
     */
    it('has appropriate length translations for UI elements', function () {
        // Close button should be short
        expect(strlen(__('categories.index_close', [], 'lt')))->toBeLessThan(20);
        expect(strlen(__('categories.index_close', [], 'en')))->toBeLessThan(20);
        
        // Filter help text can be longer but should be reasonable
        expect(strlen(__('categories.show_adjust_filters', [], 'lt')))->toBeLessThan(200);
        expect(strlen(__('categories.show_adjust_filters', [], 'en')))->toBeLessThan(200);
    });
});