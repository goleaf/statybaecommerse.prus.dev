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
                'normal_settings' => [
                    'single'     => 'Nustatymas',
                    'plural'     => 'Nustatymai',
                    'navigation' => 'Nustatymai',
                    'tabs'       => [
                        'label'   => 'Skirtukai',
                        'all'     => 'Visi',
                        'string'  => 'Tekstas',
                        'integer' => 'Sveikasis skaičius',
                        'boolean' => 'Loginis',
                        'array'   => 'Masyvas',
                        'json'    => 'JSON',
                        'public'  => 'Vieši',
                        'private' => 'Privatūs',
                        'active'  => 'Aktyvūs',
                    ],
                    'basic_information' => 'Pagrindinė informacija',
                    'settings'          => 'Nustatymai',
                    'key'               => 'Raktas',
                    'value'             => 'Reikšmė',
                    'description'       => 'Aprašymas',
                    'type'              => 'Tipas',
                    'types'             => [
                        'string'  => 'Tekstas',
                        'integer' => 'Sveikasis skaičius',
                        'boolean' => 'Loginis',
                        'array'   => 'Masyvas',
                        'json'    => 'JSON',
                    ],
                    'is_public'         => 'Ar viešas',
                    'is_public_help'    => 'Ar nustatymas gali būti naudojamas viešai',
                    'is_encrypted'      => 'Ar šifruotas',
                    'is_encrypted_help' => 'Ar nustatymas yra šifruotas',
                    'is_active'         => 'Ar aktyvus',
                    'created_at'        => 'Sukurta',
                    'updated_at'        => 'Atnaujinta',
                    'all_records'       => 'Visi įrašai',
                    'public_only'       => 'Tik vieši',
                    'private_only'      => 'Tik privatūs',
                    'encrypted_only'    => 'Tik šifruoti',
                    'unencrypted_only'  => 'Tik nešifruoti',
                    'active_only'       => 'Tik aktyvūs',
                    'inactive_only'     => 'Tik neaktyvūs',
                ],
            ],
            'en' => [
                'normal_settings' => [
                    'single'     => 'Setting',
                    'plural'     => 'Settings',
                    'navigation' => 'Settings',
                    'tabs'       => [
                        'label'   => 'Tabs',
                        'all'     => 'All',
                        'string'  => 'String',
                        'integer' => 'Integer',
                        'boolean' => 'Boolean',
                        'array'   => 'Array',
                        'json'    => 'JSON',
                        'public'  => 'Public',
                        'private' => 'Private',
                        'active'  => 'Active',
                    ],
                    'basic_information' => 'Basic Information',
                    'settings'          => 'Settings',
                    'key'               => 'Key',
                    'value'             => 'Value',
                    'description'       => 'Description',
                    'type'              => 'Type',
                    'types'             => [
                        'string'  => 'String',
                        'integer' => 'Integer',
                        'boolean' => 'Boolean',
                        'array'   => 'Array',
                        'json'    => 'JSON',
                    ],
                    'is_public'         => 'Is Public',
                    'is_public_help'    => 'Whether the setting can be used publicly',
                    'is_encrypted'      => 'Is Encrypted',
                    'is_encrypted_help' => 'Whether the setting is encrypted',
                    'is_active'         => 'Is Active',
                    'created_at'        => 'Created At',
                    'updated_at'        => 'Updated At',
                    'all_records'       => 'All Records',
                    'public_only'       => 'Public Only',
                    'private_only'      => 'Private Only',
                    'encrypted_only'    => 'Encrypted Only',
                    'unencrypted_only'  => 'Unencrypted Only',
                    'active_only'       => 'Active Only',
                    'inactive_only'     => 'Inactive Only',
                ],
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
            $allTranslations = array_replace_recursive($existing, $localeTranslations);

            // Create directory if it doesn't exist
            $dir = dirname($filePath);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Write the translations file
            $content = "<?php\n\nreturn " . var_export($allTranslations, true) . ";\n";
            file_put_contents($filePath, $content);
        }
    }
}
