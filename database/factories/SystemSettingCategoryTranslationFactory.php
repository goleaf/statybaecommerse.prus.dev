<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\SystemSettingCategory;
use App\Models\SystemSettingCategoryTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * SystemSettingCategoryTranslationFactory
 *
 * Factory for creating SystemSettingCategoryTranslation test data with comprehensive multilingual support.
 */
final class SystemSettingCategoryTranslationFactory extends Factory
{
    protected $model = SystemSettingCategoryTranslation::class;

    public function definition(): array
    {
        $locales = ['lt', 'en', 'de', 'fr', 'es'];
        $locale = fake()->unique()->randomElement($locales);

        return [
            'system_setting_category_id' => SystemSettingCategory::factory(),
            'locale' => $locale,
            'name' => $this->generateTranslatedName($locale),
            'description' => $this->generateTranslatedDescription($locale),
        ];
    }

    public function lithuanian(): static
    {
        return $this->state(fn(array $attributes) => [
            'locale' => 'lt',
            'name' => fake()->randomElement([
                'Bendri nustatymai',
                'E-parduotuvės nustatymai',
                'El. pašto nustatymai',
                'Mokėjimų nustatymai',
                'Pristatymo nustatymai',
                'SEO nustatymai',
                'Saugumo nustatymai',
                'API nustatymai',
                'Išvaizdos nustatymai',
                'Pranešimų nustatymai',
            ]),
            'description' => fake()->randomElement([
                'Bendri sistemos nustatymai ir konfigūracija',
                'E-parduotuvės funkcijų nustatymai ir parametrai',
                'El. pašto siuntimo ir konfigūracijos nustatymai',
                'Mokėjimų sistemų ir procesų nustatymai',
                'Pristatymo metodų ir sąlygų nustatymai',
                'Paieškos variklių optimizacijos nustatymai',
                'Saugumo ir autentifikavimo nustatymai',
                'API prieigos ir ribojimų nustatymai',
                'Svetainės išvaizdos ir dizaino nustatymai',
                'Pranešimų sistemų ir kanalų nustatymai',
            ]),
        ]);
    }

    public function english(): static
    {
        return $this->state(fn(array $attributes) => [
            'locale' => 'en',
            'name' => fake()->randomElement([
                'General Settings',
                'E-commerce Settings',
                'Email Settings',
                'Payment Settings',
                'Shipping Settings',
                'SEO Settings',
                'Security Settings',
                'API Settings',
                'Appearance Settings',
                'Notification Settings',
            ]),
            'description' => fake()->randomElement([
                'General system settings and configuration',
                'E-commerce functionality settings and parameters',
                'Email sending and configuration settings',
                'Payment systems and process settings',
                'Shipping methods and conditions settings',
                'Search engine optimization settings',
                'Security and authentication settings',
                'API access and limitation settings',
                'Website appearance and design settings',
                'Notification systems and channels settings',
            ]),
        ]);
    }

    public function german(): static
    {
        return $this->state(fn(array $attributes) => [
            'locale' => 'de',
            'name' => fake()->randomElement([
                'Allgemeine Einstellungen',
                'E-Commerce-Einstellungen',
                'E-Mail-Einstellungen',
                'Zahlungseinstellungen',
                'Versandeinstellungen',
                'SEO-Einstellungen',
                'Sicherheitseinstellungen',
                'API-Einstellungen',
                'Erscheinungsbild-Einstellungen',
                'Benachrichtigungseinstellungen',
            ]),
            'description' => fake()->randomElement([
                'Allgemeine Systemeinstellungen und Konfiguration',
                'E-Commerce-Funktionalitätseinstellungen und Parameter',
                'E-Mail-Versand- und Konfigurationseinstellungen',
                'Zahlungssystem- und Prozesseinstellungen',
                'Versandmethoden- und Bedingungseinstellungen',
                'Suchmaschinenoptimierungseinstellungen',
                'Sicherheits- und Authentifizierungseinstellungen',
                'API-Zugriffs- und Limitierungseinstellungen',
                'Website-Erscheinungsbild- und Designeinstellungen',
                'Benachrichtigungssystem- und Kanaleinstellungen',
            ]),
        ]);
    }

    public function french(): static
    {
        return $this->state(fn(array $attributes) => [
            'locale' => 'fr',
            'name' => fake()->randomElement([
                'Paramètres généraux',
                'Paramètres e-commerce',
                'Paramètres e-mail',
                'Paramètres de paiement',
                "Paramètres d'expédition",
                'Paramètres SEO',
                'Paramètres de sécurité',
                'Paramètres API',
                "Paramètres d'apparence",
                'Paramètres de notification',
            ]),
            'description' => fake()->randomElement([
                'Paramètres généraux du système et configuration',
                'Paramètres de fonctionnalité e-commerce et paramètres',
                "Paramètres d'envoi et de configuration e-mail",
                'Paramètres de systèmes de paiement et processus',
                "Paramètres de méthodes d'expédition et conditions",
                "Paramètres d'optimisation des moteurs de recherche",
                "Paramètres de sécurité et d'authentification",
                "Paramètres d'accès API et limitations",
                "Paramètres d'apparence et de design du site web",
                'Paramètres de systèmes de notification et canaux',
            ]),
        ]);
    }

    public function spanish(): static
    {
        return $this->state(fn(array $attributes) => [
            'locale' => 'es',
            'name' => fake()->randomElement([
                'Configuración general',
                'Configuración de comercio electrónico',
                'Configuración de correo electrónico',
                'Configuración de pagos',
                'Configuración de envío',
                'Configuración SEO',
                'Configuración de seguridad',
                'Configuración API',
                'Configuración de apariencia',
                'Configuración de notificaciones',
            ]),
            'description' => fake()->randomElement([
                'Configuración general del sistema y configuración',
                'Configuración de funcionalidad de comercio electrónico y parámetros',
                'Configuración de envío y configuración de correo electrónico',
                'Configuración de sistemas de pago y procesos',
                'Configuración de métodos de envío y condiciones',
                'Configuración de optimización de motores de búsqueda',
                'Configuración de seguridad y autenticación',
                'Configuración de acceso API y limitaciones',
                'Configuración de apariencia y diseño del sitio web',
                'Configuración de sistemas de notificación y canales',
            ]),
        ]);
    }

    public function forCategory(SystemSettingCategory $category): static
    {
        return $this->state(fn(array $attributes) => [
            'system_setting_category_id' => $category->id,
        ]);
    }

    public function withLocale(string $locale): static
    {
        return $this->state(fn(array $attributes) => [
            'locale' => $locale,
            'name' => $this->generateTranslatedName($locale),
            'description' => $this->generateTranslatedDescription($locale),
        ]);
    }

    private function generateTranslatedName(string $locale): string
    {
        $names = [
            'lt' => fake()->randomElement([
                'Bendri nustatymai',
                'E-parduotuvės nustatymai',
                'El. pašto nustatymai',
                'Mokėjimų nustatymai',
                'Pristatymo nustatymai',
                'SEO nustatymai',
                'Saugumo nustatymai',
                'API nustatymai',
                'Išvaizdos nustatymai',
                'Pranešimų nustatymai',
            ]),
            'en' => fake()->randomElement([
                'General Settings',
                'E-commerce Settings',
                'Email Settings',
                'Payment Settings',
                'Shipping Settings',
                'SEO Settings',
                'Security Settings',
                'API Settings',
                'Appearance Settings',
                'Notification Settings',
            ]),
            'de' => fake()->randomElement([
                'Allgemeine Einstellungen',
                'E-Commerce-Einstellungen',
                'E-Mail-Einstellungen',
                'Zahlungseinstellungen',
                'Versandeinstellungen',
                'SEO-Einstellungen',
                'Sicherheitseinstellungen',
                'API-Einstellungen',
                'Erscheinungsbild-Einstellungen',
                'Benachrichtigungseinstellungen',
            ]),
            'fr' => fake()->randomElement([
                'Paramètres généraux',
                'Paramètres e-commerce',
                'Paramètres e-mail',
                'Paramètres de paiement',
                "Paramètres d'expédition",
                'Paramètres SEO',
                'Paramètres de sécurité',
                'Paramètres API',
                "Paramètres d'apparence",
                'Paramètres de notification',
            ]),
            'es' => fake()->randomElement([
                'Configuración general',
                'Configuración de comercio electrónico',
                'Configuración de correo electrónico',
                'Configuración de pagos',
                'Configuración de envío',
                'Configuración SEO',
                'Configuración de seguridad',
                'Configuración API',
                'Configuración de apariencia',
                'Configuración de notificaciones',
            ]),
        ];

        return $names[$locale] ?? fake()->words(2, true);
    }

    private function generateTranslatedDescription(string $locale): string
    {
        $descriptions = [
            'lt' => fake()->randomElement([
                'Bendri sistemos nustatymai ir konfigūracija',
                'E-parduotuvės funkcijų nustatymai ir parametrai',
                'El. pašto siuntimo ir konfigūracijos nustatymai',
                'Mokėjimų sistemų ir procesų nustatymai',
                'Pristatymo metodų ir sąlygų nustatymai',
            ]),
            'en' => fake()->randomElement([
                'General system settings and configuration',
                'E-commerce functionality settings and parameters',
                'Email sending and configuration settings',
                'Payment systems and process settings',
                'Shipping methods and conditions settings',
            ]),
            'de' => fake()->randomElement([
                'Allgemeine Systemeinstellungen und Konfiguration',
                'E-Commerce-Funktionalitätseinstellungen und Parameter',
                'E-Mail-Versand- und Konfigurationseinstellungen',
                'Zahlungssystem- und Prozesseinstellungen',
                'Versandmethoden- und Bedingungseinstellungen',
            ]),
            'fr' => fake()->randomElement([
                'Paramètres généraux du système et configuration',
                'Paramètres de fonctionnalité e-commerce et paramètres',
                "Paramètres d'envoi et de configuration e-mail",
                'Paramètres de systèmes de paiement et processus',
                "Paramètres de méthodes d'expédition et conditions",
            ]),
            'es' => fake()->randomElement([
                'Configuración general del sistema y configuración',
                'Configuración de funcionalidad de comercio electrónico y parámetros',
                'Configuración de envío y configuración de correo electrónico',
                'Configuración de sistemas de pago y procesos',
                'Configuración de métodos de envío y condiciones',
            ]),
        ];

        return $descriptions[$locale] ?? fake()->paragraph();
    }
}
