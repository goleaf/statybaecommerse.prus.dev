<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\MultiLanguageTabService;
use SolutionForest\TabLayoutPlugin\Components\Tabs;
use SolutionForest\TabLayoutPlugin\Components\Tabs\Tab as TabLayoutTab;
use SolutionForest\TabLayoutPlugin\Widgets\TabsWidget as BaseWidget;
use Filament\Forms;

final class MasterMultilanguageTabsWidget extends BaseWidget
{
    public $activeTab = '';
    public array $fields = [];
    public string $tabPrefix = 'master';

    public function queryString()
    {
        return ['activeTab'];
    }

    public static function tabs(Tabs $tabs): Tabs
    {
        return $tabs
            ->id('master-multilanguage-tabs')
            ->activeTab(MultiLanguageTabService::getDefaultActiveTab())
            ->persistTabInQueryString('activeTab')
            ->contained(false);
    }

    protected function schema(): array
    {
        // Default fields if none provided
        $defaultFields = [
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
                'description' => [
                    'type' => 'rich_editor',
                    'label' => __('translations.description'),
                    'toolbar' => ['bold', 'italic', 'link', 'bulletList', 'orderedList', 'h2', 'h3'],
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
            ],
        ];

        return MultiLanguageTabService::createSectionedTabs($this->fields ?: $defaultFields);
    }

    /**
     * Configure fields for the widget
     */
    public function configureFields(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * Set tab prefix for unique identification
     */
    public function setTabPrefix(string $prefix): self
    {
        $this->tabPrefix = $prefix;
        return $this;
    }
}
