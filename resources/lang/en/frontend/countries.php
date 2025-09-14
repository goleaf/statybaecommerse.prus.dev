<?php

return [
    'title' => 'Countries',
    'subtitle' => 'Browse countries by region, currency, or other criteria',
    
    'fields' => [
        'name' => 'Name',
        'name_official' => 'Official Name',
        'description' => 'Description',
        'code' => 'Code',
        'region' => 'Region',
        'subregion' => 'Subregion',
        'currency' => 'Currency',
        'phone_code' => 'Phone Code',
        'flag' => 'Flag',
        'is_eu_member' => 'EU Member',
        'vat_rate' => 'VAT Rate',
    ],
    
    'filters' => [
        'all' => 'All Countries',
        'active' => 'Active Countries',
        'eu_members' => 'EU Members',
        'by_region' => 'Filter by Region',
        'by_currency' => 'Filter by Currency',
        'with_vat' => 'With VAT',
        'without_vat' => 'Without VAT',
        'search' => 'Search',
        'search_placeholder' => 'Search countries...',
        'all_regions' => 'All Regions',
        'all_currencies' => 'All Currencies',
        'eu_members_only' => 'EU Members Only',
        'non_eu_only' => 'Non-EU Only',
        'apply_filters' => 'Apply Filters',
        'clear_filters' => 'Clear Filters',
    ],
    
    'actions' => [
        'view_details' => 'View Details',
        'select_country' => 'Select Country',
        'show_on_map' => 'Show on Map',
        'get_directions' => 'Get Directions',
        'back_to_list' => 'Back to List',
    ],
    
    'messages' => [
        'no_countries_found' => 'No countries found.',
        'loading_countries' => 'Loading countries...',
        'country_selected' => 'Country selected.',
        'error_loading' => 'Error loading countries.',
        'try_different_filters' => 'Try different filters.',
    ],
    
    'details' => [
        'title' => 'Country Information',
        'basic_info' => 'Basic Information',
        'location_info' => 'Location Information',
        'economic_info' => 'Economic Information',
        'contact_info' => 'Contact Information',
        'additional_info' => 'Additional Information',
        'major_cities' => 'Major Cities',
        'actions' => 'Actions',
    ],

    'fields' => [
        'yes' => 'Yes',
        'no' => 'No',
        'capital' => 'Capital',
    ],
    
    'regions' => [
        'europe' => 'Europe',
        'asia' => 'Asia',
        'africa' => 'Africa',
        'north_america' => 'North America',
        'south_america' => 'South America',
        'oceania' => 'Oceania',
        'antarctica' => 'Antarctica',
    ],
    
    'currencies' => [
        'eur' => 'Euro (€)',
        'usd' => 'US Dollar ($)',
        'gbp' => 'British Pound (£)',
        'jpy' => 'Japanese Yen (¥)',
        'chf' => 'Swiss Franc',
        'cad' => 'Canadian Dollar',
        'aud' => 'Australian Dollar',
        'cny' => 'Chinese Yuan',
        'rub' => 'Russian Ruble',
        'inr' => 'Indian Rupee',
    ],
    
    'statistics' => [
        'total_countries' => 'Total Countries',
        'active_countries' => 'Active Countries',
        'eu_members' => 'EU Members',
        'countries_with_vat' => 'Countries with VAT',
        'average_vat_rate' => 'Average VAT Rate',
    ],
];
