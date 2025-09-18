<?php

return [
    // Navigation
    'navigation' => [
        'label' => 'Countries',
    ],

    // Model labels
    'model' => [
        'singular' => 'Country',
        'plural' => 'Countries',
    ],

    // Sections
    'sections' => [
        'basic_information' => 'Basic Information',
        'geographic_information' => 'Geographic Information',
        'currency_economic' => 'Currency & Economic Information',
        'contact_information' => 'Contact Information',
        'additional_information' => 'Additional Information',
        'status_settings' => 'Status & Settings',
    ],

    // Fields
    'fields' => [
        'name' => 'Name',
        'name_official' => 'Official Name',
        'cca2' => 'CCA2 Code',
        'cca3' => 'CCA3 Code',
        'ccn3' => 'CCN3 Code',
        'region' => 'Region',
        'subregion' => 'Subregion',
        'latitude' => 'Latitude',
        'longitude' => 'Longitude',
        'currency_code' => 'Currency Code',
        'currency_symbol' => 'Currency Symbol',
        'vat_rate' => 'VAT Rate',
        'timezone' => 'Timezone',
        'phone_code' => 'Phone Code',
        'phone_calling_code' => 'Phone Calling Code',
        'flag' => 'Flag',
        'svg_flag' => 'SVG Flag',
        'currencies' => 'Currencies',
        'languages' => 'Languages',
        'timezones' => 'Timezones',
        'metadata' => 'Metadata',
        'description' => 'Description',
        'is_active' => 'Active',
        'is_enabled' => 'Enabled',
        'is_eu_member' => 'EU Member',
        'requires_vat' => 'Requires VAT',
        'sort_order' => 'Sort Order',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],

    // Helpers
    'helpers' => [
        'active' => 'Whether this country is active and visible',
        'enabled' => 'Whether this country is enabled for use',
        'eu_member' => 'Whether this country is a member of the European Union',
        'requires_vat' => 'Whether this country requires VAT for transactions',
        'vat_rate' => 'VAT rate as a percentage (0-100)',
        'sort_order' => 'Order for displaying countries (lower numbers appear first)',
    ],

    // Placeholders
    'placeholders' => [
        'no_flag' => 'No flag',
        'no_description' => 'No description',
    ],

    // Actions
    'actions' => [
        'activate' => 'Activate',
        'deactivate' => 'Deactivate',
        'activate_selected' => 'Activate Selected',
        'deactivate_selected' => 'Deactivate Selected',
        'activated_successfully' => 'Countries activated successfully',
        'deactivated_successfully' => 'Countries deactivated successfully',
    ],

    // Filters
    'filters' => [
        'active' => 'Active Status',
        'enabled' => 'Enabled Status',
        'eu_member' => 'EU Member Status',
        'requires_vat' => 'VAT Required Status',
        'region' => 'Region',
        'currency_code' => 'Currency Code',
    ],

    // Statistics
    'stats' => [
        'total_countries' => 'Total Countries',
        'active_countries' => 'Active Countries',
        'eu_members' => 'EU Members',
        'countries_with_vat' => 'Countries with VAT',
    ],
];
