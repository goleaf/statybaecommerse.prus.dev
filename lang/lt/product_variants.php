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
        'settings' => 'Nustatymai',
        'all' => 'Visi Variantai',
        'in_stock' => 'Yra Atsargose',
        'low_stock' => 'Mažai Atsargų',
        'out_of_stock' => 'Nėra Atsargų',
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
        'settings' => 'Varianto Nustatymai',
    ],

    // Fields
    'fields' => [
        'product' => 'Produktas',
        'name' => 'Varianto Pavadinimas',
        'sku' => 'SKU',
        'variant_sku_suffix' => 'SKU Priesaga',
        'barcode' => 'Brūkšninis Kodas',
        'variant_type' => 'Varianto Tipas',
        'is_default_variant' => 'Numatytasis Variantas',
        'size' => 'Dydis',
        'size_unit' => 'Dydžio Vienetas',
        'size_display' => 'Dydžio Rodymas',
        'size_price_modifier' => 'Dydžio Kainos Modifikatorius',
        'size_weight_modifier' => 'Dydžio Svorio Modifikatorius',
        'price' => 'Kaina',
        'compare_price' => 'Palyginimo Kaina',
        'cost_price' => 'Savikaina',
        'track_inventory' => 'Sekti Atsargas',
        'allow_backorder' => 'Leisti Užsakymą be Atsargų',
        'quantity' => 'Kiekis',
        'low_stock_threshold' => 'Mažų Atsargų Slenkstis',
        'attribute' => 'Atributas',
        'attribute_value' => 'Atributo Reikšmė',
        'image' => 'Paveikslėlis',
        'alt_text' => 'Alternatyvus Tekstas',
        'sort_order' => 'Rūšiavimo Eiliškumas',
        'is_primary' => 'Pagrindinis Paveikslėlis',
        'is_enabled' => 'Įjungtas',
        'position' => 'Pozicija',
        'variant_metadata' => 'Varianto Metaduomenys',
        'metadata_key' => 'Raktas',
        'metadata_value' => 'Reikšmė',
        'stock_status' => 'Atsargų Būsena',
        'created_at' => 'Sukurta',
    ],

    // Variant Types
    'variant_types' => [
        'size' => 'Dydis',
        'color' => 'Spalva',
        'material' => 'Medžiaga',
        'style' => 'Stilius',
        'custom' => 'Pritaikytas',
    ],

    // Stock Status
    'stock_status' => [
        'in_stock' => 'Yra Atsargose',
        'low_stock' => 'Mažai Atsargų',
        'out_of_stock' => 'Nėra Atsargų',
        'not_tracked' => 'Nesekama',
    ],

    // Actions
    'actions' => [
        'add_attribute' => 'Pridėti Atributą',
        'add_image' => 'Pridėti Paveikslėlį',
        'add_metadata' => 'Pridėti Metaduomenis',
        'set_default' => 'Nustatyti kaip Numatytąjį',
        'enable' => 'Įjungti',
        'disable' => 'Išjungti',
    ],

    // Messages
    'messages' => [
        'created_successfully' => 'Produkto variantas sėkmingai sukurtas',
        'created_successfully_description' => 'Produkto variantas buvo sukurtas ir paruoštas naudojimui',
        'updated_successfully' => 'Produkto variantas sėkmingai atnaujintas',
        'updated_successfully_description' => 'Produkto variantas buvo atnaujintas su jūsų pakeitimais',
        'set_as_default_success' => 'Variantas sėkmingai nustatytas kaip numatytasis',
        'bulk_enable_success' => 'Pasirinkti variantai buvo įjungti',
        'bulk_disable_success' => 'Pasirinkti variantai buvo išjungti',
    ],

    // Validation Messages
    'validation' => [
        'name_required' => 'Varianto pavadinimas yra privalomas',
        'sku_required' => 'SKU yra privalomas',
        'sku_unique' => 'Šis SKU jau naudojamas',
        'product_required' => 'Produktas yra privalomas',
        'price_required' => 'Kaina yra privaloma',
        'price_numeric' => 'Kaina turi būti skaičius',
        'quantity_numeric' => 'Kiekis turi būti skaičius',
    ],

    // Help Text
    'help' => [
        'variant_sku_suffix' => 'Neprivaloma priesaga, kuri bus pridėta prie bazinio SKU',
        'size_price_modifier' => 'Papildoma kaina šiam dydžiui (gali būti neigiama nuolaidoms)',
        'size_weight_modifier' => 'Papildomas svoris šiam dydžiui',
        'low_stock_threshold' => 'Perspėjimas, kai atsargos nukrenta žemiau šio skaičiaus',
        'variant_metadata' => 'Papildomi pritaikyti duomenys šiam variantui',
    ],

    // Frontend Messages
    'messages' => [
        'select_variant' => 'Prašome pasirinkti variantą',
        'no_variant_selected' => 'Prašome pasirinkti variantą prieš pridėdami į krepšelį',
        'variant_not_available' => 'Šis variantas nėra prieinamas pirkimui',
        'insufficient_stock' => 'Nepakanka atsargų pasirinktam kiekiui',
        'added_to_cart' => 'Produktas sėkmingai pridėtas į krepšelį',
        'not_available' => 'Neprieinamas',
        'out_of_stock' => 'Nėra atsargų',
        'low_stock' => 'Liko tik :quantity vienetų',
        'in_stock' => ':quantity prieinama',
    ],

    // Frontend Actions
    'actions' => [
        'add_to_cart' => 'Pridėti į Krepšelį',
    ],
];
