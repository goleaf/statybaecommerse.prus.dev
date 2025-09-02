<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\MultiLanguageTabService;
use SolutionForest\TabLayoutPlugin\Components\Tabs;
use SolutionForest\TabLayoutPlugin\Components\Tabs\Tab as TabLayoutTab;
use SolutionForest\TabLayoutPlugin\Widgets\TabsWidget as BaseWidget;
use Filament\Forms;

/**
 * Universal Multilanguage Tabs Widget
 * 
 * This widget can be used across ALL admin modules to provide consistent
 * multilanguage tab functionality with Lithuanian-first approach.
 */
final class UniversalMultilanguageTabsWidget extends BaseWidget
{
    public $activeTab = '';
    public array $configuredFields = [];
    public string $tabId = 'universal';

    public function queryString()
    {
        return ['activeTab'];
    }

    public static function tabs(Tabs $tabs): Tabs
    {
        return $tabs
            ->id('universal-multilanguage-tabs')
            ->activeTab(MultiLanguageTabService::getDefaultActiveTab())
            ->persistTabInQueryString('activeTab')
            ->contained(false);
    }

    protected function schema(): array
    {
        // Universal fields that work for most content types
        $universalFields = [
            'content_management' => [
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
                'title' => [
                    'type' => 'text',
                    'label' => __('translations.title'),
                    'maxLength' => 255,
                ],
                'description' => [
                    'type' => 'rich_editor',
                    'label' => __('translations.description'),
                    'toolbar' => [
                        'bold', 'italic', 'link', 'bulletList', 'orderedList', 
                        'h2', 'h3', 'blockquote', 'table'
                    ],
                ],
                'summary' => [
                    'type' => 'textarea',
                    'label' => __('translations.summary'),
                    'maxLength' => 500,
                    'rows' => 2,
                ],
                'content' => [
                    'type' => 'rich_editor',
                    'label' => __('translations.content'),
                    'toolbar' => [
                        'bold', 'italic', 'link', 'bulletList', 'orderedList', 
                        'h2', 'h3', 'blockquote', 'codeBlock', 'table'
                    ],
                ],
            ],
            'seo_optimization' => [
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
        ];

        return MultiLanguageTabService::createSectionedTabs(
            $this->configuredFields ?: $universalFields
        );
    }

    /**
     * Configure custom fields for specific use cases
     */
    public function configureFields(array $fields): self
    {
        $this->configuredFields = $fields;
        return $this;
    }

    /**
     * Set unique tab ID for this instance
     */
    public function setTabId(string $id): self
    {
        $this->tabId = $id;
        return $this;
    }

    /**
     * Quick setup for basic content (name, description, SEO)
     */
    public static function basicContent(): array
    {
        return MultiLanguageTabService::createSectionedTabs([
            'basic_information' => [
                'name' => [
                    'type' => 'text',
                    'label' => __('translations.name'),
                    'required' => true,
                    'maxLength' => 255,
                ],
                'description' => [
                    'type' => 'textarea',
                    'label' => __('translations.description'),
                    'maxLength' => 1000,
                    'rows' => 3,
                ],
            ],
            'seo_information' => [
                'seo_title' => [
                    'type' => 'text',
                    'label' => __('translations.seo_title'),
                    'maxLength' => 255,
                ],
                'seo_description' => [
                    'type' => 'textarea',
                    'label' => __('translations.seo_description'),
                    'maxLength' => 300,
                    'rows' => 3,
                ],
            ],
        ]);
    }

    /**
     * Quick setup for rich content (name, slug, description, content)
     */
    public static function richContent(): array
    {
        return MultiLanguageTabService::createSectionedTabs([
            'content_management' => [
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
                ],
                'description' => [
                    'type' => 'textarea',
                    'label' => __('translations.description'),
                    'maxLength' => 1000,
                    'rows' => 3,
                ],
                'content' => [
                    'type' => 'rich_editor',
                    'label' => __('translations.content'),
                    'toolbar' => [
                        'bold', 'italic', 'link', 'bulletList', 'orderedList', 
                        'h2', 'h3', 'blockquote', 'codeBlock', 'table'
                    ],
                ],
            ],
        ]);
    }
}
