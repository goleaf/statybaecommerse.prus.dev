<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SystemSettingCategory;
use App\Models\SystemSettingCategoryTranslation;
use Illuminate\Database\Seeder;

final class SystemSettingCategoryTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $this->createCategoryTranslations();
    }

    private function createCategoryTranslations(): void
    {
        $categories = SystemSettingCategory::all();

        foreach ($categories as $category) {
            $this->createTranslationsForCategory($category);
        }
    }

    private function createTranslationsForCategory(SystemSettingCategory $category): void
    {
        $translations = [
            'lt' => $this->getLithuanianTranslations($category->slug),
            'en' => $this->getEnglishTranslations($category->slug),
            'de' => $this->getGermanTranslations($category->slug),
            'fr' => $this->getFrenchTranslations($category->slug),
            'es' => $this->getSpanishTranslations($category->slug),
        ];

        foreach ($translations as $locale => $translation) {
            if ($translation) {
                SystemSettingCategoryTranslation::updateOrCreate(
                    [
                        'system_setting_category_id' => $category->id,
                        'locale' => $locale,
                    ],
                    [
                        'name' => $translation['name'],
                        'description' => $translation['description'],
                    ]
                );
            }
        }
    }

    private function getLithuanianTranslations(string $slug): ?array
    {
        $translations = [
            'general' => [
                'name' => 'Bendri nustatymai',
                'description' => 'Bendri sistemos nustatymai ir konfigūracija, įskaitant programos pavadinimą, kalbą, valiutą ir kitus pagrindinius parametrus.',
            ],
            'ecommerce' => [
                'name' => 'E-parduotuvės nustatymai',
                'description' => 'E-parduotuvės funkcijų nustatymai ir parametrai, įskaitant PVM tarifus, minimalius užsakymų dydžius ir atsargų valdymą.',
            ],
            'email' => [
                'name' => 'El. pašto nustatymai',
                'description' => 'El. pašto siuntimo ir konfigūracijos nustatymai, įskaitant siuntėjo duomenis, SMTP konfigūraciją ir pranešimų šablonus.',
            ],
            'payment' => [
                'name' => 'Mokėjimų nustatymai',
                'description' => 'Mokėjimų sistemų ir procesų nustatymai, įskaitant mokėjimo būdus, automatinį užsakymų patvirtinimą ir saugumo reikalavimus.',
            ],
            'shipping' => [
                'name' => 'Pristatymo nustatymai',
                'description' => 'Pristatymo metodų ir sąlygų nustatymai, įskaitant pristatymo kainas, laikotarpius ir geografinius apribojimus.',
            ],
            'seo' => [
                'name' => 'SEO nustatymai',
                'description' => 'Paieškos variklių optimizacijos nustatymai, įskaitant meta duomenis, sitemap konfigūraciją ir analitikos integraciją.',
            ],
            'security' => [
                'name' => 'Saugumo nustatymai',
                'description' => 'Saugumo ir autentifikavimo nustatymai, įskaitant slaptažodžių reikalavimus, sesijų valdymą ir prieigos kontrolę.',
            ],
            'api' => [
                'name' => 'API nustatymai',
                'description' => 'API prieigos ir ribojimų nustatymai, įskaitant autentifikavimą, greičio limitus ir prieigos kontrolę.',
            ],
            'appearance' => [
                'name' => 'Išvaizdos nustatymai',
                'description' => 'Svetainės išvaizdos ir dizaino nustatymai, įskaitant spalvas, logotipus ir vartotojo sąsajos elementus.',
            ],
            'notifications' => [
                'name' => 'Pranešimų nustatymai',
                'description' => 'Pranešimų sistemų ir kanalų nustatymai, įskaitant el. pašto, SMS ir push pranešimus.',
            ],
        ];

        return $translations[$slug] ?? null;
    }

    private function getEnglishTranslations(string $slug): ?array
    {
        $translations = [
            'general' => [
                'name' => 'General Settings',
                'description' => 'General system settings and configuration including application name, language, currency and other core parameters.',
            ],
            'ecommerce' => [
                'name' => 'E-commerce Settings',
                'description' => 'E-commerce functionality settings and parameters including tax rates, minimum order amounts and inventory management.',
            ],
            'email' => [
                'name' => 'Email Settings',
                'description' => 'Email sending and configuration settings including sender information, SMTP configuration and notification templates.',
            ],
            'payment' => [
                'name' => 'Payment Settings',
                'description' => 'Payment systems and process settings including payment methods, automatic order approval and security requirements.',
            ],
            'shipping' => [
                'name' => 'Shipping Settings',
                'description' => 'Shipping methods and conditions settings including shipping costs, timeframes and geographic restrictions.',
            ],
            'seo' => [
                'name' => 'SEO Settings',
                'description' => 'Search engine optimization settings including meta data, sitemap configuration and analytics integration.',
            ],
            'security' => [
                'name' => 'Security Settings',
                'description' => 'Security and authentication settings including password requirements, session management and access control.',
            ],
            'api' => [
                'name' => 'API Settings',
                'description' => 'API access and limitation settings including authentication, rate limits and access control.',
            ],
            'appearance' => [
                'name' => 'Appearance Settings',
                'description' => 'Website appearance and design settings including colors, logos and user interface elements.',
            ],
            'notifications' => [
                'name' => 'Notification Settings',
                'description' => 'Notification systems and channels settings including email, SMS and push notifications.',
            ],
        ];

        return $translations[$slug] ?? null;
    }

    private function getGermanTranslations(string $slug): ?array
    {
        $translations = [
            'general' => [
                'name' => 'Allgemeine Einstellungen',
                'description' => 'Allgemeine Systemeinstellungen und Konfiguration einschließlich Anwendungsname, Sprache, Währung und andere Kernparameter.',
            ],
            'ecommerce' => [
                'name' => 'E-Commerce-Einstellungen',
                'description' => 'E-Commerce-Funktionalitätseinstellungen und Parameter einschließlich Steuersätze, Mindestbestellbeträge und Bestandsverwaltung.',
            ],
            'email' => [
                'name' => 'E-Mail-Einstellungen',
                'description' => 'E-Mail-Versand- und Konfigurationseinstellungen einschließlich Absenderinformationen, SMTP-Konfiguration und Benachrichtigungsvorlagen.',
            ],
            'payment' => [
                'name' => 'Zahlungseinstellungen',
                'description' => 'Zahlungssystem- und Prozesseinstellungen einschließlich Zahlungsmethoden, automatischer Bestellgenehmigung und Sicherheitsanforderungen.',
            ],
            'shipping' => [
                'name' => 'Versandeinstellungen',
                'description' => 'Versandmethoden- und Bedingungseinstellungen einschließlich Versandkosten, Zeitrahmen und geografischen Beschränkungen.',
            ],
            'seo' => [
                'name' => 'SEO-Einstellungen',
                'description' => 'Suchmaschinenoptimierungseinstellungen einschließlich Meta-Daten, Sitemap-Konfiguration und Analytics-Integration.',
            ],
            'security' => [
                'name' => 'Sicherheitseinstellungen',
                'description' => 'Sicherheits- und Authentifizierungseinstellungen einschließlich Passwortanforderungen, Sitzungsverwaltung und Zugriffskontrolle.',
            ],
            'api' => [
                'name' => 'API-Einstellungen',
                'description' => 'API-Zugriffs- und Limitierungseinstellungen einschließlich Authentifizierung, Geschwindigkeitsbegrenzungen und Zugriffskontrolle.',
            ],
            'appearance' => [
                'name' => 'Erscheinungsbild-Einstellungen',
                'description' => 'Website-Erscheinungsbild- und Designeinstellungen einschließlich Farben, Logos und Benutzeroberflächenelementen.',
            ],
            'notifications' => [
                'name' => 'Benachrichtigungseinstellungen',
                'description' => 'Benachrichtigungssystem- und Kanaleinstellungen einschließlich E-Mail-, SMS- und Push-Benachrichtigungen.',
            ],
        ];

        return $translations[$slug] ?? null;
    }

    private function getFrenchTranslations(string $slug): ?array
    {
        $translations = [
            'general' => [
                'name' => 'Paramètres généraux',
                'description' => 'Paramètres généraux du système et configuration incluant le nom de l\'application, la langue, la devise et autres paramètres principaux.',
            ],
            'ecommerce' => [
                'name' => 'Paramètres e-commerce',
                'description' => 'Paramètres de fonctionnalité e-commerce et paramètres incluant les taux de taxe, les montants minimum de commande et la gestion des stocks.',
            ],
            'email' => [
                'name' => 'Paramètres e-mail',
                'description' => 'Paramètres d\'envoi et de configuration e-mail incluant les informations d\'expéditeur, la configuration SMTP et les modèles de notification.',
            ],
            'payment' => [
                'name' => 'Paramètres de paiement',
                'description' => 'Paramètres de systèmes de paiement et processus incluant les méthodes de paiement, l\'approbation automatique des commandes et les exigences de sécurité.',
            ],
            'shipping' => [
                'name' => 'Paramètres d\'expédition',
                'description' => 'Paramètres de méthodes d\'expédition et conditions incluant les coûts d\'expédition, les délais et les restrictions géographiques.',
            ],
            'seo' => [
                'name' => 'Paramètres SEO',
                'description' => 'Paramètres d\'optimisation des moteurs de recherche incluant les métadonnées, la configuration du plan du site et l\'intégration d\'analytique.',
            ],
            'security' => [
                'name' => 'Paramètres de sécurité',
                'description' => 'Paramètres de sécurité et d\'authentification incluant les exigences de mot de passe, la gestion de session et le contrôle d\'accès.',
            ],
            'api' => [
                'name' => 'Paramètres API',
                'description' => 'Paramètres d\'accès et de limitation API incluant l\'authentification, les limites de débit et le contrôle d\'accès.',
            ],
            'appearance' => [
                'name' => 'Paramètres d\'apparence',
                'description' => 'Paramètres d\'apparence et de design du site web incluant les couleurs, logos et éléments d\'interface utilisateur.',
            ],
            'notifications' => [
                'name' => 'Paramètres de notification',
                'description' => 'Paramètres de systèmes de notification et canaux incluant les notifications e-mail, SMS et push.',
            ],
        ];

        return $translations[$slug] ?? null;
    }

    private function getSpanishTranslations(string $slug): ?array
    {
        $translations = [
            'general' => [
                'name' => 'Configuración general',
                'description' => 'Configuración general del sistema incluyendo nombre de aplicación, idioma, moneda y otros parámetros principales.',
            ],
            'ecommerce' => [
                'name' => 'Configuración de comercio electrónico',
                'description' => 'Configuración de funcionalidad de comercio electrónico y parámetros incluyendo tasas de impuestos, montos mínimos de pedido y gestión de inventario.',
            ],
            'email' => [
                'name' => 'Configuración de correo electrónico',
                'description' => 'Configuración de envío y configuración de correo electrónico incluyendo información del remitente, configuración SMTP y plantillas de notificación.',
            ],
            'payment' => [
                'name' => 'Configuración de pagos',
                'description' => 'Configuración de sistemas de pago y procesos incluyendo métodos de pago, aprobación automática de pedidos y requisitos de seguridad.',
            ],
            'shipping' => [
                'name' => 'Configuración de envío',
                'description' => 'Configuración de métodos de envío y condiciones incluyendo costos de envío, plazos y restricciones geográficas.',
            ],
            'seo' => [
                'name' => 'Configuración SEO',
                'description' => 'Configuración de optimización de motores de búsqueda incluyendo metadatos, configuración de sitemap e integración de analíticas.',
            ],
            'security' => [
                'name' => 'Configuración de seguridad',
                'description' => 'Configuración de seguridad y autenticación incluyendo requisitos de contraseña, gestión de sesiones y control de acceso.',
            ],
            'api' => [
                'name' => 'Configuración API',
                'description' => 'Configuración de acceso y limitación API incluyendo autenticación, límites de velocidad y control de acceso.',
            ],
            'appearance' => [
                'name' => 'Configuración de apariencia',
                'description' => 'Configuración de apariencia y diseño del sitio web incluyendo colores, logotipos y elementos de interfaz de usuario.',
            ],
            'notifications' => [
                'name' => 'Configuración de notificaciones',
                'description' => 'Configuración de sistemas de notificación y canales incluyendo notificaciones por correo electrónico, SMS y push.',
            ],
        ];

        return $translations[$slug] ?? null;
    }
}
