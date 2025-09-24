<?php

declare(strict_types=1);

return [
    // Navigation and Labels
    'title' => 'Produktų Variantai',
    'plural' => 'Produktų Variantai',
    'single' => 'Produkto Variantas',
    'navigation_label' => 'Produktų Variantai',
    'navigation_group' => 'Produktai',

    // Tabs
    'tabs' => [
        'main' => 'Pagrindinė Informacija',
        'basic_information' => 'Pagrindinė Informacija',
        'size_information' => 'Dydžio Informacija',
        'pricing' => 'Kainodara',
        'inventory' => 'Atsargos',
        'attributes' => 'Atributai',
        'images' => 'Paveikslėliai',
        'analytics' => 'Analitika',
        'seo' => 'SEO',
        'settings' => 'Nustatymai',
        'all' => 'Visi Variantai',
        'in_stock' => 'Yra Atsargose',
        'low_stock' => 'Mažai Atsargų',
        'out_of_stock' => 'Nėra Atsargų',
        'on_sale' => 'Akcija',
        'featured' => 'Rekomenduojami',
        'new' => 'Nauji',
        'bestsellers' => 'Populiariausi',
        'size_variants' => 'Dydžio Variantai',
        'color_variants' => 'Spalvų Variantai',
        'default_variants' => 'Numatytieji Variantai',
    ],

    // Sections
    'sections' => [
        'basic_information' => 'Pagrindinė Informacija',
        'size_information' => 'Dydžio Informacija',
        'pricing' => 'Kainodaros Informacija',
        'inventory' => 'Atsargų Valdymas',
        'attributes' => 'Varianto Atributai',
        'images' => 'Varianto Paveikslėliai',
        'analytics' => 'Analitikos Duomenys',
        'seo' => 'SEO Nustatymai',
        'settings' => 'Varianto Nustatymai',
    ],

    // Fields
    'fields' => [
        'product' => 'Produktas',
        'name' => 'Varianto Pavadinimas',
        'variant_name_lt' => 'Varianto Pavadinimas (LT)',
        'variant_name_en' => 'Varianto Pavadinimas (EN)',
        'description_lt' => 'Aprašymas (LT)',
        'description_en' => 'Aprašymas (EN)',
        'sku' => 'SKU',
        'variant_sku_suffix' => 'Varianto SKU Priesaga',
        'barcode' => 'Brūkšninis Kodas',
        'variant_type' => 'Varianto Tipas',
        'is_default_variant' => 'Numatytasis Variantas',
        'size' => 'Dydis',
        'size_unit' => 'Dydžio Matavimo Vnt.',
        'size_display' => 'Dydžio Rodymas',
        'size_price_modifier' => 'Dydžio Kainos Modifikatorius',
        'size_weight_modifier' => 'Dydžio Svorio Modifikatorius',
        'price' => 'Kaina',
        'compare_price' => 'Palyginimo Kaina',
        'cost_price' => 'Savikaina',
        'wholesale_price' => 'Didmeninė Kaina',
        'member_price' => 'Nario Kaina',
        'promotional_price' => 'Akcijos Kaina',
        'is_on_sale' => 'Ar Yra Akcija',
        'sale_start_date' => 'Akcijos Pradžios Data',
        'sale_end_date' => 'Akcijos Pabaigos Data',
        'stock_quantity' => 'Atsargų Kiekis',
        'reserved_quantity' => 'Rezervuotas Kiekis',
        'available_quantity' => 'Prieinamas Kiekis',
        'sold_quantity' => 'Parduotas Kiekis',
        'weight' => 'Svoris',
        'track_inventory' => 'Sekti Atsargas',
        'is_enabled' => 'Įjungtas',
        'attributes' => 'Atributai',
        'images' => 'Paveikslėliai',
        'seo_title_lt' => 'SEO Antraštė (LT)',
        'seo_title_en' => 'SEO Antraštė (EN)',
        'seo_description_lt' => 'SEO Aprašymas (LT)',
        'seo_description_en' => 'SEO Aprašymas (EN)',
        'views_count' => 'Peržiūrų Skaičius',
        'clicks_count' => 'Paspaudimų Skaičius',
        'conversion_rate' => 'Konversijos Rodiklis',
        'is_featured' => 'Rekomenduojamas',
        'is_new' => 'Naujas',
        'is_bestseller' => 'Populiariausias',
        'variant_combination_hash' => 'Varianto Kombinacijos Maiša',
        'stock_status' => 'Atsargų Būsena',
        'description' => 'Aprašymas',
        'quantity' => 'Kiekis',
        'price_type' => 'Kainos Tipas',
        'update_type' => 'Atnaujinimo Tipas',
        'update_value' => 'Atnaujinimo Reikšmė',
        'change_reason' => 'Pakeitimo Priežastis',
        'apply_to_sale_items' => 'Taikyti Akcijos Prekėms',
        'update_compare_price' => 'Atnaujinti Palyginimo Kainą',
        'compare_price_action' => 'Palyginimo Kainos Veiksmas',
        'compare_price_value' => 'Palyginimo Kainos Reikšmė',
        'set_sale_period' => 'Nustatyti Akcijos Laikotarpį',
        'rating' => 'Įvertinimas',
        'available' => 'Prieinama',
        'badges' => 'Žymės',
    ],

    // Variant Types
    'variant_types' => [
        'size' => 'Dydis',
        'color' => 'Spalva',
        'material' => 'Medžiaga',
        'style' => 'Stilius',
        'custom' => 'Pasirinktinis',
    ],

    // Messages
    'messages' => [
        'no_variant_selected' => 'Nepasirinktas joks variantas.',
        'variant_not_available' => 'Šis variantas šiuo metu neprieinamas pirkimui.',
        'insufficient_stock' => 'Nepakanka atsargų pasirinktam kiekiui.',
        'added_to_cart' => 'Prekė pridėta į krepšelį!',
        'select_variant' => 'Pasirinkite variantą, kad pamatytumėte detales.',
        'out_of_stock' => 'Nėra sandėlyje',
        'low_stock' => 'Mažai liko (liko :quantity)',
        'in_stock' => 'Yra sandėlyje (liko :quantity)',
        'max_quantity' => 'Maksimalus kiekis',
    ],

    // Frontend Actions
    'actions' => [
        'bulk_price_update' => 'Masinis Kainų Atnaujinimas',
        'export' => 'Eksportuoti',
        'import' => 'Importuoti',
        'add_to_cart' => 'Pridėti į Krepšelį',
        'add_to_comparison' => 'Pridėti į Palyginimą',
        'compare' => 'Palyginti',
        'view_details' => 'Peržiūrėti Detales',
        'actions' => 'Veiksmai',
    ],

    // Price Types
    'price_types' => [
        'regular' => 'Įprasta Kaina',
        'wholesale' => 'Didmeninė Kaina',
        'member' => 'Nario Kaina',
        'promotional' => 'Akcijos Kaina',
    ],

    // Update Types
    'update_types' => [
        'fixed_amount' => 'Fiksuota Suma',
        'percentage' => 'Procentais',
        'multiply_by' => 'Padauginti Iš',
        'set_to' => 'Nustatyti Į',
    ],

    // Compare Price Actions
    'compare_price_actions' => [
        'no_change' => 'Nekeisti',
        'match_new_price' => 'Suderinti Su Nauja Kaina',
        'increase_by_percentage' => 'Padidinti Procentais',
        'increase_by_fixed_amount' => 'Padidinti Fiksuota Suma',
    ],

    // Help Text
    'help' => [
        'update_value' => 'Įveskite skaičių. Pvz., 10 (procentams) arba 5.50 (sumai)',
    ],

    // Placeholders
    'placeholders' => [
        'change_reason' => 'Įveskite pakeitimo priežastį (neprivaloma)',
    ],

    // Modals
    'modals' => [
        'bulk_price_update_heading' => 'Masinis Kainų Atnaujinimas',
        'bulk_price_update_description' => 'Atnaujinsite kainas visiems pasirinktiems variantams.',
    ],

    // Notifications
    'notifications' => [
        'bulk_update_success' => 'Kainos Sėkmingai Atnaujintos',
        'bulk_update_success_body' => 'Atnaujinta :updated variantų kainos. Praleista :skipped variantų.',
    ],

    // Stats
    'stats' => [
        'total_variants' => 'Iš Viso Variantų',
        'all_variants' => 'Visi Variantai',
        'in_stock' => 'Yra Atsargose',
        'available_variants' => 'Prieinami Variantai',
        'low_stock' => 'Mažai Atsargų',
        'need_restocking' => 'Reikia Papildyti',
        'out_of_stock' => 'Nėra Atsargų',
        'unavailable_variants' => 'Neprieinami Variantai',
        'total_views' => 'Iš Viso Peržiūrų',
        'product_page_views' => 'Produkto Puslapio Peržiūros',
        'total_clicks' => 'Iš Viso Paspaudimų',
        'variant_selections' => 'Varianto Pasirinkimai',
        'conversion_rate' => 'Konversijos Rodiklis',
        'views_to_sales' => 'Peržiūros Į Pardavimus',
        'total_stock' => 'Iš Viso Atsargų',
        'all_variants_stock' => 'Visi Variantai',
        'available_stock' => 'Prieinamos Atsargos',
        'ready_for_sale' => 'Paruošta Pardavimui',
        'reserved_stock' => 'Rezervuotos Atsargos',
        'pending_orders' => 'Laukiantys Užsakymai',
        'sold_stock' => 'Parduotos Atsargos',
        'total_sold' => 'Iš Viso Parduota',
        'low_stock_alerts' => 'Mažai Atsargų Įspėjimai',
        'stock_value' => 'Atsargų Vertė',
        'total_inventory_value' => 'Bendroji Atsargų Vertė',
        'average_price' => 'Vidutinė Kaina',
        'highest_price' => 'Aukščiausia Kaina',
        'most_expensive' => 'Brangiausias',
        'lowest_price' => 'Žemiausia Kaina',
        'most_affordable' => 'Pigiausias',
        'on_sale' => 'Akcija',
        'discounted_variants' => 'Su Nuolaida',
        'average_discount' => 'Vidutinė Nuolaida',
        'sale_discount' => 'Akcijos Nuolaida',
        'total_revenue' => 'Bendrosios Pajamos',
        'from_sales' => 'Iš Pardavimų',
        'price_range_under_50' => 'Iki 50€',
        'under_50_euros' => 'Iki 50 Eurų',
        'price_range_50_100' => '50-100€',
        'between_50_100_euros' => 'Tarp 50-100 Eurų',
    ],

    // Comparison
    'comparison' => [
        'title' => 'Variantų Palyginimas',
        'subtitle' => 'Palyginami :count variantai',
        'clear_all' => 'Išvalyti Visus',
        'variant' => 'Variantas',
        'remove' => 'Pašalinti',
        'no_variants_selected' => 'Nepasirinkta Jokių Variantų',
        'select_variants_to_compare' => 'Pasirinkite variantus palyginimui',
    ],

    // Stock Status
    'stock_status' => [
        'in_stock' => 'Yra Atsargose',
        'low_stock' => 'Mažai Atsargų',
        'out_of_stock' => 'Nėra Atsargų',
        'not_tracked' => 'Neseka',
    ],

    // Badges
    'badges' => [
        'new' => 'Naujas',
        'featured' => 'Rekomenduojamas',
        'bestseller' => 'Populiariausias',
        'sale' => 'Akcija',
    ],

    // Showcase
    'showcase' => [
        'title' => 'Produktų Variantų Demonstracija',
        'subtitle' => 'Pamatykite visus galimus variantų funkcijas ir galimybes',
        'select_product' => 'Pasirinkite Produktą',
        'variants_count' => 'variantai',
        'brand' => 'Prekės Ženklas',
        'analytics_title' => 'Analitikos Duomenys',
        'total_variants' => 'Iš Viso Variantų',
        'in_stock' => 'Yra Atsargose',
        'low_stock' => 'Mažai Atsargų',
        'out_of_stock' => 'Nėra Atsargų',
        'on_sale' => 'Akcija',
        'featured' => 'Rekomenduojami',
        'average_price' => 'Vidutinė Kaina',
        'highest_price' => 'Aukščiausia Kaina',
        'lowest_price' => 'Žemiausia Kaina',
        'variant_selection' => 'Varianto Pasirinkimas',
        'selected_variant' => 'Pasirinktas Variantas',
        'variant_attributes' => 'Varianto Atributai',
        'all_variants' => 'Visi Variantai',
    ],
];
