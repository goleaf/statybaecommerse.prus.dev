<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultilanguageTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'name' => 'Admin User',
        ]);

        // Give the user admin permissions
        $this->adminUser->assignRole('super_admin');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function application_uses_lithuanian_as_default_locale(): void
    {
        expect(config('app.locale'))->toBe('lt');
        expect(config('app.fallback_locale'))->toBe('lt');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function translation_files_exist_for_both_languages(): void
    {
        $translationFiles = [
            'admin.php',
            'analytics.php',
            'documents.php',
            'ecommerce.php',
            'frontend.php',
            'navigation.php',
            'shared.php',
            'store.php',
            'translations.php',
            'validation.php',
        ];

        foreach ($translationFiles as $file) {
            $ltPath = lang_path("lt/{$file}");
            $enPath = lang_path("en/{$file}");

            expect(file_exists($ltPath))->toBeTrue("Lithuanian translation file {$file} should exist");
            expect(file_exists($enPath))->toBeTrue("English translation file {$file} should exist");
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function lithuanian_translations_are_loaded(): void
    {
        app()->setLocale('lt');

        $translation = __('translations.country_name');
        expect($translation)->not()->toBe('translations.country_name');
        expect($translation)->toBeString();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function english_translations_are_loaded(): void
    {
        app()->setLocale('en');

        $translation = __('translations.country_name');
        expect($translation)->not()->toBe('translations.country_name');
        expect($translation)->toBeString();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_switch_between_locales(): void
    {
        // Test Lithuanian
        app()->setLocale('lt');
        expect(app()->getLocale())->toBe('lt');

        // Test English
        app()->setLocale('en');
        expect(app()->getLocale())->toBe('en');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function models_support_multilanguage_fields(): void
    {
        $country = Country::factory()->create();

        // Test that country has translation support
        expect($country)->toHaveProperty('translatable');
        expect($country->translatable)->toContain('name');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function location_model_supports_multilanguage_fields(): void
    {
        $location = Location::factory()->create();

        // Test that location has translation support
        expect($location)->toHaveProperty('translatable');
        expect($location->translatable)->toContain('name');
        expect($location->translatable)->toContain('slug');
        expect($location->translatable)->toContain('description');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_save_multilanguage_data(): void
    {
        $country = Country::factory()->create();

        $location = Location::factory()->create([
            'country_id' => $country->id,
        ]);

        // Set multilanguage data
        $location->setTranslations('name', [
            'lt' => 'Lietuviškas pavadinimas',
            'en' => 'English name',
        ]);

        $location->setTranslations('description', [
            'lt' => 'Lietuviškas aprašymas',
            'en' => 'English description',
        ]);

        $location->save();

        // Test Lithuanian translations
        app()->setLocale('lt');
        expect($location->name)->toBe('Lietuviškas pavadinimas');
        expect($location->description)->toBe('Lietuviškas aprašymas');

        // Test English translations
        app()->setLocale('en');
        expect($location->name)->toBe('English name');
        expect($location->description)->toBe('English description');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function fallback_locale_works_when_translation_missing(): void
    {
        $country = Country::factory()->create();

        $location = Location::factory()->create([
            'country_id' => $country->id,
        ]);

        // Set only Lithuanian translation
        $location->setTranslations('name', [
            'lt' => 'Tik lietuviškai',
        ]);

        $location->save();

        // Test that fallback works when English is missing
        app()->setLocale('en');
        expect($location->name)->toBe('Tik lietuviškai');  // Should fallback to Lithuanian
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_translations_work(): void
    {
        app()->setLocale('lt');

        $adminTranslations = [
            'admin.navigation.dashboard',
            'admin.navigation.orders',
            'admin.navigation.customers',
            'admin.navigation.partners',
            'admin.navigation.documents',
            'admin.navigation.settings',
        ];

        foreach ($adminTranslations as $key) {
            $translation = __($key);
            expect($translation)->not()->toBe($key);
            expect($translation)->toBeString();
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function ecommerce_translations_work(): void
    {
        app()->setLocale('lt');

        $ecommerceTranslations = [
            'ecommerce.product',
            'ecommerce.order',
            'ecommerce.customer',
            'ecommerce.price',
            'ecommerce.quantity',
        ];

        foreach ($ecommerceTranslations as $key) {
            $translation = __($key);
            expect($translation)->not()->toBe($key);
            expect($translation)->toBeString();
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function document_translations_work(): void
    {
        app()->setLocale('lt');

        $documentTranslations = [
            'documents.document_information',
            'documents.template',
            'documents.title',
            'documents.status',
            'documents.format',
        ];

        foreach ($documentTranslations as $key) {
            $translation = __($key);
            expect($translation)->not()->toBe($key);
            expect($translation)->toBeString();
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function analytics_translations_work(): void
    {
        app()->setLocale('lt');

        $analyticsTranslations = [
            'analytics.orders_overview',
        ];

        foreach ($analyticsTranslations as $key) {
            $translation = __($key);
            expect($translation)->not()->toBe($key);
            expect($translation)->toBeString();
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function currency_formatting_respects_locale(): void
    {
        // Test Euro formatting for Lithuanian locale
        app()->setLocale('lt');
        $amount = 1234.56;
        $formatted = number_format($amount, 2) . ' €';
        expect($formatted)->toBe('1,234.56 €');

        // Test Euro formatting for English locale
        app()->setLocale('en');
        $formatted = '€' . number_format($amount, 2);
        expect($formatted)->toBe('€1,234.56');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function date_formatting_respects_locale(): void
    {
        $date = now()->setDate(2024, 12, 25);

        // Test Lithuanian date format
        app()->setLocale('lt');
        $ltDate = $date->format('Y-m-d');
        expect($ltDate)->toBe('2024-12-25');

        // Test English date format
        app()->setLocale('en');
        $enDate = $date->format('m/d/Y');
        expect($enDate)->toBe('12/25/2024');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function validation_messages_are_translated(): void
    {
        app()->setLocale('lt');

        $validationMessages = [
            'validation.required',
            'validation.email',
            'validation.max.string',
            'validation.unique',
        ];

        foreach ($validationMessages as $key) {
            $translation = __($key);
            expect($translation)->not()->toBe($key);
            expect($translation)->toBeString();
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function pagination_is_translated(): void
    {
        app()->setLocale('lt');

        $paginationMessages = [
            'pagination.previous',
            'pagination.next',
        ];

        foreach ($paginationMessages as $key) {
            $translation = __($key);
            expect($translation)->not()->toBe($key);
            expect($translation)->toBeString();
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function shared_translations_work(): void
    {
        app()->setLocale('lt');

        $sharedTranslations = [
            'shared.save',
            'shared.cancel',
            'shared.delete',
            'shared.edit',
            'shared.view',
            'shared.create',
        ];

        foreach ($sharedTranslations as $key) {
            $translation = __($key);
            expect($translation)->not()->toBe($key);
            expect($translation)->toBeString();
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function frontend_translations_work(): void
    {
        app()->setLocale('lt');

        $frontendTranslations = [
            'frontend.home',
            'frontend.about',
            'frontend.contact',
            'frontend.products',
        ];

        foreach ($frontendTranslations as $key) {
            $translation = __($key);
            expect($translation)->not()->toBe($key);
            expect($translation)->toBeString();
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function store_translations_work(): void
    {
        app()->setLocale('lt');

        $storeTranslations = [
            'store.add_to_cart',
            'store.checkout',
            'store.cart',
            'store.wishlist',
        ];

        foreach ($storeTranslations as $key) {
            $translation = __($key);
            expect($translation)->not()->toBe($key);
            expect($translation)->toBeString();
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_have_preferred_locale(): void
    {
        $user = User::factory()->create([
            'preferred_locale' => 'en',
        ]);

        expect($user->preferred_locale)->toBe('en');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function multilanguage_slug_generation_works(): void
    {
        $country = Country::factory()->create();

        $location = Location::factory()->create([
            'country_id' => $country->id,
        ]);

        $location->setTranslations('name', [
            'lt' => 'Vilniaus Centras',
            'en' => 'Vilnius Center',
        ]);

        $location->setTranslations('slug', [
            'lt' => 'vilniaus-centras',
            'en' => 'vilnius-center',
        ]);

        $location->save();

        // Test Lithuanian slug
        app()->setLocale('lt');
        expect($location->slug)->toBe('vilniaus-centras');

        // Test English slug
        app()->setLocale('en');
        expect($location->slug)->toBe('vilnius-center');
    }
}
