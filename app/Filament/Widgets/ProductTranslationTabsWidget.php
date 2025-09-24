<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\MultiLanguageTabService;
use SolutionForest\TabLayoutPlugin\Components\Tabs;
use SolutionForest\TabLayoutPlugin\Widgets\TabsWidget as BaseWidget;

final class ProductTranslationTabsWidget extends BaseWidget
{
    public $activeTab = '';

    public function queryString()
    {
        return ['activeTab'];
    }

    public static function tabs(Tabs $tabs): Tabs
    {
        return $tabs
            ->id('product-translation-tabs')
            ->activeTab(MultiLanguageTabService::getDefaultActiveTab())
            ->persistTabInQueryString('activeTab')
            ->contained(false);
    }

    protected function schema(): array
    {
        return MultiLanguageTabService::createSectionedTabs([
            'basic_information' => [
                'name' => [
                    'type' => 'text',
                    'label' => __('translations.name'),
                    'required' => true,
                    'maxLength' => 255,
                ],
                'slug' => [
                    'type' => 'text',
                    'label' => __('translations.slug'),
                    'required' => true,
                    'maxLength' => 255,
                    'placeholder' => __('translations.slug_auto_generated'),
                ],
                'summary' => [
                    'type' => 'textarea',
                    'label' => __('translations.summary'),
                    'maxLength' => 500,
                    'rows' => 2,
                    'placeholder' => __('translations.product_summary_help'),
                ],
                'description' => [
                    'type' => 'rich_editor',
                    'label' => __('translations.description'),
                    'toolbar' => [
                        'bold', 'italic', 'link', 'bulletList', 'orderedList',
                        'h2', 'h3', 'blockquote', 'codeBlock', 'table',
                    ],
                ],
            ],
            'seo_information' => [
                'seo_title' => [
                    'type' => 'text',
                    'label' => __('translations.seo_title'),
                    'maxLength' => 255,
                    'placeholder' => __('translations.seo_title_help'),
                ],
                'seo_description' => [
                    'type' => 'textarea',
                    'label' => __('translations.seo_description'),
                    'maxLength' => 300,
                    'rows' => 3,
                    'placeholder' => __('translations.seo_description_help'),
                ],
                'meta_keywords' => [
                    'type' => 'text',
                    'label' => __('translations.meta_keywords'),
                    'maxLength' => 255,
                    'placeholder' => __('translations.meta_keywords_help'),
                ],
            ],
        ]);
    }
}
