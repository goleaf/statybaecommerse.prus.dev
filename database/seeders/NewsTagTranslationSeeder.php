<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class NewsTagTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $translations = [
            'lt' => [
                'admin.news_tags.plural' => 'Naujienų žymės',
                'admin.news_tags.single' => 'Naujienų žymė',
                'admin.news_tags.form.sections.basic_information' => 'Pagrindinė informacija',
                'admin.news_tags.form.fields.name' => 'Pavadinimas',
                'admin.news_tags.form.fields.slug' => 'URL slug',
                'admin.news_tags.form.fields.description' => 'Aprašymas',
                'admin.news_tags.form.fields.is_visible' => 'Ar matoma',
                'admin.news_tags.form.fields.sort_order' => 'Rūšiavimo eilė',
                'admin.news_tags.form.fields.color' => 'Spalva',
                'admin.news_tags.form.sections.translations' => 'Vertimai',
                'admin.news_tags.form.fields.translations' => 'Kalbų vertimai',
                'admin.news_tags.form.fields.locale' => 'Kalba',
                'admin.news_tags.form.sections.metadata' => 'Metaduomenys',
                'admin.news_tags.form.fields.created_at' => 'Sukurta',
                'admin.news_tags.form.fields.updated_at' => 'Atnaujinta',
                'admin.news_tags.form.fields.news_count' => 'Naujienų skaičius',
                'admin.news_tags.table.name' => 'Pavadinimas',
                'admin.news_tags.table.slug' => 'URL slug',
                'admin.news_tags.table.description' => 'Aprašymas',
                'admin.news_tags.table.color' => 'Spalva',
                'admin.news_tags.table.is_visible' => 'Matoma',
                'admin.news_tags.table.sort_order' => 'Eilė',
                'admin.news_tags.table.news_count' => 'Naujienų sk.',
                'admin.news_tags.table.created_at' => 'Sukurta',
                'admin.news_tags.filters.active' => 'Aktyvūs',
                'admin.news_tags.filters.inactive' => 'Neaktyvūs',
                'admin.news_tags.filters.with_news' => 'Su naujienomis',
                'admin.news_tags.filters.without_news' => 'Be naujienų',
                'admin.news_tags.filters.color' => 'Spalva',
                'admin.news_tags.filters.recent' => 'Naujausi',
                'admin.news_tags.actions.activate' => 'Aktyvuoti',
                'admin.news_tags.actions.deactivate' => 'Deaktyvuoti',
                'admin.news_tags.actions.duplicate' => 'Kopijuoti',
                'admin.news_tags.actions.bulk_activate' => 'Masinis aktyvavimas',
                'admin.news_tags.actions.bulk_deactivate' => 'Masinis deaktyvavimas',
                'admin.news_tags.actions.bulk_duplicate' => 'Masinis kopijavimas',
                'admin.news_tags.activated_successfully' => 'Žymė sėkmingai aktyvuota',
                'admin.news_tags.deactivated_successfully' => 'Žymė sėkmingai deaktyvuota',
                'admin.news_tags.duplicated_successfully' => 'Žymė sėkmingai nukopijuota',
                'admin.news_tags.bulk_activated_successfully' => 'Žymės sėkmingai aktyvuotos',
                'admin.news_tags.bulk_deactivated_successfully' => 'Žymės sėkmingai deaktyvuotos',
                'admin.news_tags.bulk_duplicated_successfully' => 'Žymės sėkmingai nukopijuotos',
            ],
            'en' => [
                'admin.news_tags.plural' => 'News Tags',
                'admin.news_tags.single' => 'News Tag',
                'admin.news_tags.form.sections.basic_information' => 'Basic Information',
                'admin.news_tags.form.fields.name' => 'Name',
                'admin.news_tags.form.fields.slug' => 'URL slug',
                'admin.news_tags.form.fields.description' => 'Description',
                'admin.news_tags.form.fields.is_visible' => 'Is Visible',
                'admin.news_tags.form.fields.sort_order' => 'Sort Order',
                'admin.news_tags.form.fields.color' => 'Color',
                'admin.news_tags.form.sections.translations' => 'Translations',
                'admin.news_tags.form.fields.translations' => 'Language Translations',
                'admin.news_tags.form.fields.locale' => 'Locale',
                'admin.news_tags.form.sections.metadata' => 'Metadata',
                'admin.news_tags.form.fields.created_at' => 'Created At',
                'admin.news_tags.form.fields.updated_at' => 'Updated At',
                'admin.news_tags.form.fields.news_count' => 'News Count',
                'admin.news_tags.table.name' => 'Name',
                'admin.news_tags.table.slug' => 'URL slug',
                'admin.news_tags.table.description' => 'Description',
                'admin.news_tags.table.color' => 'Color',
                'admin.news_tags.table.is_visible' => 'Visible',
                'admin.news_tags.table.sort_order' => 'Order',
                'admin.news_tags.table.news_count' => 'News Count',
                'admin.news_tags.table.created_at' => 'Created At',
                'admin.news_tags.filters.active' => 'Active',
                'admin.news_tags.filters.inactive' => 'Inactive',
                'admin.news_tags.filters.with_news' => 'With News',
                'admin.news_tags.filters.without_news' => 'Without News',
                'admin.news_tags.filters.color' => 'Color',
                'admin.news_tags.filters.recent' => 'Recent',
                'admin.news_tags.actions.activate' => 'Activate',
                'admin.news_tags.actions.deactivate' => 'Deactivate',
                'admin.news_tags.actions.duplicate' => 'Duplicate',
                'admin.news_tags.actions.bulk_activate' => 'Bulk Activate',
                'admin.news_tags.actions.bulk_deactivate' => 'Bulk Deactivate',
                'admin.news_tags.actions.bulk_duplicate' => 'Bulk Duplicate',
                'admin.news_tags.activated_successfully' => 'Tag activated successfully',
                'admin.news_tags.deactivated_successfully' => 'Tag deactivated successfully',
                'admin.news_tags.duplicated_successfully' => 'Tag duplicated successfully',
                'admin.news_tags.bulk_activated_successfully' => 'Tags activated successfully',
                'admin.news_tags.bulk_deactivated_successfully' => 'Tags deactivated successfully',
                'admin.news_tags.bulk_duplicated_successfully' => 'Tags duplicated successfully',
            ],
        ];

        // Since this project uses Laravel's built-in translation system,
        // we'll create language files instead of database entries
        foreach ($translations as $locale => $localeTranslations) {
            $filePath = lang_path("{$locale}/admin.php");

            // Load existing translations if file exists
            $existing = [];
            if (file_exists($filePath)) {
                $existing = include $filePath;
            }

            // Merge with new translations
            $allTranslations = array_merge_recursive($existing, $localeTranslations);

            // Create directory if it doesn't exist
            $dir = dirname($filePath);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Write the translations file
            $content = "<?php\n\nreturn ".var_export($allTranslations, true).";\n";
            file_put_contents($filePath, $content);
        }
    }
}
