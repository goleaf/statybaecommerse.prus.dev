<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class NormalSettingTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $translations = [
            'lt' => [
                'normal_settings.single' => 'Nustatymas',
                'normal_settings.plural' => 'Nustatymai',
                'normal_settings.navigation' => 'Nustatymai',
                'normal_settings.tabs' => 'Skirtukai',
                'normal_settings.basic_information' => 'Pagrindinė informacija',
                'normal_settings.settings' => 'Nustatymai',
                'normal_settings.key' => 'Raktas',
                'normal_settings.value' => 'Reikšmė',
                'normal_settings.description' => 'Aprašymas',
                'normal_settings.type' => 'Tipas',
                'normal_settings.types.string' => 'Tekstas',
                'normal_settings.types.integer' => 'Sveikasis skaičius',
                'normal_settings.types.boolean' => 'Loginis',
                'normal_settings.types.array' => 'Masyvas',
                'normal_settings.types.json' => 'JSON',
                'normal_settings.is_public' => 'Ar viešas',
                'normal_settings.is_public_help' => 'Ar nustatymas gali būti naudojamas viešai',
                'normal_settings.is_encrypted' => 'Ar šifruotas',
                'normal_settings.is_encrypted_help' => 'Ar nustatymas yra šifruotas',
                'normal_settings.is_active' => 'Ar aktyvus',
                'normal_settings.created_at' => 'Sukurta',
                'normal_settings.updated_at' => 'Atnaujinta',
                'normal_settings.all_records' => 'Visi įrašai',
                'normal_settings.public_only' => 'Tik vieši',
                'normal_settings.private_only' => 'Tik privatūs',
                'normal_settings.encrypted_only' => 'Tik šifruoti',
                'normal_settings.unencrypted_only' => 'Tik nešifruoti',
                'normal_settings.active_only' => 'Tik aktyvūs',
                'normal_settings.inactive_only' => 'Tik neaktyvūs',
            ],
            'en' => [
                'normal_settings.single' => 'Setting',
                'normal_settings.plural' => 'Settings',
                'normal_settings.navigation' => 'Settings',
                'normal_settings.tabs' => 'Tabs',
                'normal_settings.basic_information' => 'Basic Information',
                'normal_settings.settings' => 'Settings',
                'normal_settings.key' => 'Key',
                'normal_settings.value' => 'Value',
                'normal_settings.description' => 'Description',
                'normal_settings.type' => 'Type',
                'normal_settings.types.string' => 'String',
                'normal_settings.types.integer' => 'Integer',
                'normal_settings.types.boolean' => 'Boolean',
                'normal_settings.types.array' => 'Array',
                'normal_settings.types.json' => 'JSON',
                'normal_settings.is_public' => 'Is Public',
                'normal_settings.is_public_help' => 'Whether the setting can be used publicly',
                'normal_settings.is_encrypted' => 'Is Encrypted',
                'normal_settings.is_encrypted_help' => 'Whether the setting is encrypted',
                'normal_settings.is_active' => 'Is Active',
                'normal_settings.created_at' => 'Created At',
                'normal_settings.updated_at' => 'Updated At',
                'normal_settings.all_records' => 'All Records',
                'normal_settings.public_only' => 'Public Only',
                'normal_settings.private_only' => 'Private Only',
                'normal_settings.encrypted_only' => 'Encrypted Only',
                'normal_settings.unencrypted_only' => 'Unencrypted Only',
                'normal_settings.active_only' => 'Active Only',
                'normal_settings.inactive_only' => 'Inactive Only',
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
