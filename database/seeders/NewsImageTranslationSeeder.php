<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class NewsImageTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $translations = [
            'lt' => [
                'admin.news_images.navigation_label' => 'Naujienų paveikslėliai',
                'admin.news_images.plural_model_label' => 'Naujienų paveikslėliai',
                'admin.news_images.model_label' => 'Naujienų paveikslėlis',
                'admin.news_images.tabs' => 'Skirtukai',
                'admin.news_images.basic_information' => 'Pagrindinė informacija',
                'admin.news_images.basic_information_description' => 'Naujienų paveikslėlio pagrindinė informacija',
                'admin.news_images.news' => 'Naujiena',
                'admin.news_images.file_path' => 'Failo kelias',
                'admin.news_images.alt_text' => 'Alternatyvus tekstas',
                'admin.news_images.alt_text_help' => 'Paveikslėlio alternatyvus tekstas SEO tikslams',
                'admin.news_images.sort_order' => 'Rūšiavimo eilė',
                'admin.news_images.sort_order_help' => 'Paveikslėlių rodymo eilė naujienoje',
                'admin.news_images.caption' => 'Antraštė',
                'admin.news_images.caption_help' => 'Paveikslėlio antraštė',
                'admin.news_images.is_featured' => 'Ar pagrindinis',
                'admin.news_images.is_featured_help' => 'Ar tai pagrindinis paveikslėlis',
                'admin.news_images.file_size' => 'Failo dydis',
                'admin.news_images.mime_type' => 'MIME tipas',
                'admin.news_images.technical_details' => 'Techninė informacija',
                'admin.news_images.technical_details_description' => 'Paveikslėlio techninė informacija',
                'admin.news_images.width' => 'Plotis',
                'admin.news_images.height' => 'Aukštis',
                'admin.news_images.file_info' => 'Failo informacija',
                'admin.news_images.dimensions' => 'Matmenys',
                'admin.news_images.seo_metadata' => 'SEO metaduomenys',
                'admin.news_images.seo_metadata_description' => 'Paveikslėlio SEO metaduomenys',
                'admin.news_images.alt_text_seo_help' => 'Alternatyvus tekstas paieškos sistemoms',
                'admin.news_images.caption_seo_help' => 'Antraštė SEO tikslams',
                'admin.news_images.image' => 'Paveikslėlis',
                'admin.news_images.no_alt_text' => 'Nėra alternatyvaus teksto',
                'admin.news_images.no_caption' => 'Nėra antraštės',
                'admin.news_images.large_files' => 'Dideli failai',
                'admin.news_images.recent_uploads' => 'Naujausi įkėlimai',
                'admin.news_images.no_alt_text' => 'Be alternatyvaus teksto',
                'admin.news_images.duplicate' => 'Kopijuoti',
                'admin.news_images.download' => 'Atsisiųsti',
                'admin.news_images.set_featured' => 'Nustatyti kaip pagrindinį',
                'admin.news_images.unset_featured' => 'Pašalinti iš pagrindinių',
                'admin.news_images.reorder' => 'Pertvarkyti',
                'admin.common.yes' => 'Taip',
                'admin.common.no' => 'Ne',
                'admin.common.view' => 'Peržiūrėti',
                'admin.common.edit' => 'Redaguoti',
                'admin.common.delete' => 'Ištrinti',
                'admin.common.actions' => 'Veiksmai',
                'admin.common.delete_selected' => 'Ištrinti pasirinktus',
                'admin.common.created_at' => 'Sukurta',
                'admin.common.updated_at' => 'Atnaujinta',
                'admin.common.all' => 'Visi',
            ],
            'en' => [
                'admin.news_images.navigation_label' => 'News Images',
                'admin.news_images.plural_model_label' => 'News Images',
                'admin.news_images.model_label' => 'News Image',
                'admin.news_images.tabs' => 'Tabs',
                'admin.news_images.basic_information' => 'Basic Information',
                'admin.news_images.basic_information_description' => 'Basic information about the news image',
                'admin.news_images.news' => 'News',
                'admin.news_images.file_path' => 'File Path',
                'admin.news_images.alt_text' => 'Alt Text',
                'admin.news_images.alt_text_help' => 'Alternative text for the image for SEO purposes',
                'admin.news_images.sort_order' => 'Sort Order',
                'admin.news_images.sort_order_help' => 'Display order of images in the news',
                'admin.news_images.caption' => 'Caption',
                'admin.news_images.caption_help' => 'Image caption',
                'admin.news_images.is_featured' => 'Is Featured',
                'admin.news_images.is_featured_help' => 'Whether this is the main image',
                'admin.news_images.file_size' => 'File Size',
                'admin.news_images.mime_type' => 'MIME Type',
                'admin.news_images.technical_details' => 'Technical Details',
                'admin.news_images.technical_details_description' => 'Technical information about the image',
                'admin.news_images.width' => 'Width',
                'admin.news_images.height' => 'Height',
                'admin.news_images.file_info' => 'File Information',
                'admin.news_images.dimensions' => 'Dimensions',
                'admin.news_images.seo_metadata' => 'SEO Metadata',
                'admin.news_images.seo_metadata_description' => 'SEO metadata for the image',
                'admin.news_images.alt_text_seo_help' => 'Alternative text for search engines',
                'admin.news_images.caption_seo_help' => 'Caption for SEO purposes',
                'admin.news_images.image' => 'Image',
                'admin.news_images.no_alt_text' => 'No alt text',
                'admin.news_images.no_caption' => 'No caption',
                'admin.news_images.large_files' => 'Large Files',
                'admin.news_images.recent_uploads' => 'Recent Uploads',
                'admin.news_images.no_alt_text' => 'No alt text',
                'admin.news_images.duplicate' => 'Duplicate',
                'admin.news_images.download' => 'Download',
                'admin.news_images.set_featured' => 'Set as Featured',
                'admin.news_images.unset_featured' => 'Unset Featured',
                'admin.news_images.reorder' => 'Reorder',
                'admin.common.yes' => 'Yes',
                'admin.common.no' => 'No',
                'admin.common.view' => 'View',
                'admin.common.edit' => 'Edit',
                'admin.common.delete' => 'Delete',
                'admin.common.actions' => 'Actions',
                'admin.common.delete_selected' => 'Delete Selected',
                'admin.common.created_at' => 'Created At',
                'admin.common.updated_at' => 'Updated At',
                'admin.common.all' => 'All',
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
