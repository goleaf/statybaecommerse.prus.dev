<?php

return [
    // Navigation
    'navigation' => [],
    // Model labels
    'model' => [
        'singular' => 'Prekių ženklas',
    ],
    // Sections
    'sections' => [
        'basic_information' => 'Pagrindinė informacija',
        'media' => 'Medijos',
        'seo' => 'SEO nustatymai',
        'settings' => 'Nustatymai',
        'translations' => 'Vertimai',
    ],
    // Fields
    'fields' => [
        'name' => 'Pavadinimas',
        'slug' => 'Slug',
        'description' => 'Aprašymas',
        'website' => 'Svetainė',
        'is_enabled' => 'Įjungtas',
        'is_active' => 'Aktyvus',
        'is_visible' => 'Matomas',
        'is_featured' => 'Išskirtinis',
        'logo' => 'Logotipas',
        'banner' => 'Baneris',
        'seo_title' => 'SEO pavadinimas',
        'seo_description' => 'SEO aprašymas',
        'translations' => 'Vertimai',
        'locale' => 'Kalba',
        'translations_count' => 'Vertimai',
        'products_count' => 'Produktai',
        'created_at' => 'Sukurta',
        'updated_at' => 'Atnaujinta',
    ],
    // Helpers
    'helpers' => [
        'enabled' => 'Ar šis prekių ženklas yra aktyvus ir matomas',
        'seo_title' => 'Rekomenduojamas ilgis: 50-60 simbolių',
        'seo_description' => 'Rekomenduojamas ilgis: 150-160 simbolių',
    ],
    // Placeholders
    'placeholders' => [
        'no_website' => 'Nėra svetainės',
    ],
    // Actions
    'actions' => [
        'add_translation' => 'Pridėti vertimą',
        'enable' => 'Įjungti',
        'disable' => 'Išjungti',
        'enable_selected' => 'Įjungti pasirinktus',
        'disable_selected' => 'Išjungti pasirinktus',
        'activate' => 'Aktyvuoti',
        'deactivate' => 'Deaktyvuoti',
        'feature' => 'Pažymėti kaip išskirtinį',
        'unfeature' => 'Pašalinti iš išskirtinių',
        'feature_selected' => 'Pažymėti pasirinktus kaip išskirtinius',
        'unfeature_selected' => 'Pašalinti pasirinktus iš išskirtinių',
        'manage_translations' => 'Valdyti vertimus',
        'bulk_actions' => 'Masiniai veiksmai',
    ],
    // Filters
    'filters' => [
        'enabled_only' => 'Tik įjungti',
        'featured_only' => 'Tik išskirtiniai',
        'not_featured' => 'Neišskirtiniai',
        'visible_only' => 'Tik matomi',
        'hidden_only' => 'Tik paslėpti',
        'with_products' => 'Su produktais',
        'without_products' => 'Be produktų',
        'with_website' => 'Su svetaine',
        'recent' => 'Naujausi',
        'has_products' => 'Turi produktų',
        'has_translations' => 'Turi vertimų',
        'translation_locale' => 'Vertimo kalba',
    ],
    // Statistics
    'stats' => [
        'total_brands' => 'Iš viso prekių ženklų',
        'total_brands_description' => 'Visi sistemos prekių ženklai',
        'enabled_brands' => 'Įjungti prekių ženklai',
        'enabled_brands_description' => 'Aktyvūs ir matomi prekių ženklai',
    ],
    // Widgets
    'widgets' => [
        'brand_overview' => 'Prekių ženklų apžvalga',
        'brand_performance' => 'Prekių ženklų veikla',
        'brand_analytics' => 'Prekių ženklų analitika',
    ],
    // Empty states
    'empty_states' => [
        'no_brands' => 'Prekių ženklų nerasta',
        'no_enabled_brands' => 'Nėra įjungtų prekių ženklų',
        'no_brands_with_products' => 'Nėra prekių ženklų su produktais',
    ],
    // Messages
    'messages' => [
        'created' => 'Prekių ženklas sėkmingai sukurtas',
        'updated' => 'Prekių ženklas sėkmingai atnaujintas',
        'deleted' => 'Prekių ženklas sėkmingai ištrintas',
        'enabled' => 'Prekių ženklas sėkmingai įjungtas',
        'disabled' => 'Prekių ženklas sėkmingai išjungtas',
    ],

    // Notifications
    'notifications' => [
        'activated' => 'Prekių ženklas sėkmingai aktyvuotas',
        'deactivated' => 'Prekių ženklas sėkmingai deaktyvuotas',
        'featured_enabled' => 'Prekių ženklas pažymėtas kaip išskirtinis',
        'featured_disabled' => 'Prekių ženklas nebėra išskirtinis',
        'bulk_enabled' => 'Pasirinkti prekių ženklai įjungti',
        'bulk_disabled' => 'Pasirinkti prekių ženklai išjungti',
        'bulk_featured' => 'Pasirinkti prekių ženklai pažymėti kaip išskirtiniai',
        'bulk_unfeatured' => 'Pasirinkti prekių ženklai pašalinti iš išskirtinių',
    ],
    // Validation
    'validation' => [
        'name_required' => 'Prekių ženklo pavadinimas yra privalomas',
        'name_max' => 'Prekių ženklo pavadinimas negali viršyti 255 simbolių',
        'slug_required' => 'Prekių ženklo slug yra privalomas',
        'slug_unique' => 'Prekių ženklo slug turi būti unikalus',
        'slug_alpha_dash' => 'Prekių ženklo slug gali turėti tik raides, skaičius, brūkšnelius ir pabraukimus',
        'description_max' => 'Prekių ženklo aprašymas negali viršyti 1000 simbolių',
        'website_url' => 'Svetainė turi būti galiojantis URL',
        'website_max' => 'Svetainė negali viršyti 255 simbolių',
        'seo_title_max' => 'SEO pavadinimas negali viršyti 60 simbolių',
        'seo_description_max' => 'SEO aprašymas negali viršyti 160 simbolių',
    ],
];
