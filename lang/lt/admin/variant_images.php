<?php

return [
    // Navigation and Labels
    'navigation_label' => 'Varianto Nuotraukos',
    'plural_model_label' => 'Varianto Nuotraukos',
    'model_label' => 'Varianto Nuotrauka',

    // Form Sections
    'basic_information' => 'Pagrindinė Informacija',
    'image_details' => 'Nuotraukos Detalės',
    'display_settings' => 'Rodymo Nustatymai',
    'metadata' => 'Metaduomenys',

    // Form Fields
    'variant' => 'Produkto Variantas',
    'variant_info' => 'Varianto Informacija',
    'image' => 'Nuotrauka',
    'alt_text' => 'Alternatyvus Tekstas',
    'description' => 'Aprašymas',
    'sort_order' => 'Rikiavimo Eiliškumas',
    'is_primary' => 'Ar Pagrindinė',
    'is_active' => 'Ar Aktyvi',
    'file_size' => 'Failo Dydis',
    'dimensions' => 'Matmenys',
    'created_by' => 'Sukūrė',

    // Table Columns
    'sku' => 'SKU',
    'created_at' => 'Sukurta',
    'updated_at' => 'Atnaujinta',

    // Help Text
    'image_help' => 'Įkelkite nuotraukos failą (JPEG, PNG, WebP). Maksimalus dydis: 5MB.',
    'alt_text_help' => 'Alternatyvus tekstas prieinamumui ir SEO.',
    'description_help' => 'Neprivalomas nuotraukos aprašymas.',
    'sort_order_help' => 'Eiliškumas, kuriuo nuotraukos rodomos (mažesni skaičiai pirmi).',
    'is_primary_help' => 'Tik viena nuotrauka per variantą gali būti pagrindinė.',
    'is_active_help' => 'Neaktyvios nuotraukos paslėptos iš frontend.',

    // Actions
    'set_as_primary' => 'Nustatyti kaip Pagrindinę',
    'activate' => 'Aktyvuoti',
    'deactivate' => 'Deaktyvuoti',
    'duplicate' => 'Kopijuoti',
    'reorder_images' => 'Perrikiuoti Nuotraukas',

    // Bulk Actions
    'activate_selected' => 'Aktyvuoti Pasirinktas',
    'deactivate_selected' => 'Deaktyvuoti Pasirinktas',
    'set_primary_selected' => 'Nustatyti Pagrindines Pasirinktas',

    // Filters
    'primary_only' => 'Tik Pagrindinės',
    'non_primary_only' => 'Tik Nepagrindinės',
    'active_only' => 'Tik Aktyvios',
    'inactive_only' => 'Tik Neaktyvios',
    'created_from' => 'Sukurta Nuo',
    'created_until' => 'Sukurta Iki',

    // Notifications
    'set_as_primary_successfully' => 'Nuotrauka nustatyta kaip pagrindinė sėkmingai.',
    'activated_successfully' => 'Nuotrauka aktyvuota sėkmingai.',
    'deactivated_successfully' => 'Nuotrauka deaktyvuota sėkmingai.',
    'duplicated_successfully' => 'Nuotrauka nukopijuota sėkmingai.',
    'reordered_successfully' => 'Nuotraukos perrikiuotos sėkmingai.',
    'bulk_activated_successfully' => 'Pasirinktos nuotraukos aktyvuotos sėkmingai.',
    'bulk_deactivated_successfully' => 'Pasirinktos nuotraukos deaktyvuotos sėkmingai.',
    'bulk_primary_set_successfully' => 'Pagrindinės nuotraukos nustatytos sėkmingai.',

    // Validation Messages
    'variant_required' => 'Prašome pasirinkti produkto variantą.',
    'image_required' => 'Prašome įkelti nuotrauką.',
    'image_invalid_format' => 'Neteisingas nuotraukos formatas. Prašome įkelti JPEG, PNG arba WebP failą.',
    'image_too_large' => 'Nuotraukos failas per didelis. Maksimalus dydis yra 5MB.',
    'alt_text_max_length' => 'Alternatyvus tekstas negali viršyti 255 simbolių.',
    'description_max_length' => 'Aprašymas negali viršyti 1000 simbolių.',
    'sort_order_numeric' => 'Rikiavimo eiliškumas turi būti skaičius.',
    'sort_order_min' => 'Rikiavimo eiliškumas turi būti bent 0.',

    // Status Messages
    'no_images_found' => 'Varianto nuotraukų nerasta.',
    'no_primary_image' => 'Šiam variantui nėra nustatyta pagrindinė nuotrauka.',
    'multiple_primary_images' => 'Aptikta kelios pagrindinės nuotraukos. Tik viena turėtų būti pagrindinė.',
    'image_not_found' => 'Nuotraukos failas nerastas.',
    'image_upload_failed' => 'Nepavyko įkelti nuotraukos.',
    'image_delete_failed' => 'Nepavyko ištrinti nuotraukos failo.',

    // Statistics
    'total_images' => 'Iš Viso Nuotraukų',
    'primary_images' => 'Pagrindinės Nuotraukos',
    'active_images' => 'Aktyvios Nuotraukos',
    'inactive_images' => 'Neaktyvios Nuotraukos',
    'total_file_size' => 'Bendras Failų Dydis',
    'average_file_size' => 'Vidutinis Failų Dydis',

    // Image Processing
    'processing_image' => 'Apdorojama nuotrauka...',
    'image_processed' => 'Nuotrauka apdorota sėkmingai.',
    'image_processing_failed' => 'Nepavyko apdoroti nuotraukos.',
    'generating_thumbnails' => 'Generuojami miniatiūros...',
    'thumbnails_generated' => 'Miniatiūros sugeneruotos sėkmingai.',
    'thumbnail_generation_failed' => 'Nepavyko sugeneruoti miniatiūrų.',

    // File Management
    'file_not_found' => 'Failas nerastas.',
    'file_already_exists' => 'Failas jau egzistuoja.',
    'file_size_exceeded' => 'Failo dydis viršijo maksimalų limitą.',
    'invalid_file_type' => 'Neteisingas failo tipas.',
    'file_corrupted' => 'Failas atrodo sugadintas.',

    // Bulk Operations
    'bulk_operation_in_progress' => 'Masinė operacija vyksta...',
    'bulk_operation_completed' => 'Masinė operacija baigta sėkmingai.',
    'bulk_operation_failed' => 'Masinė operacija nepavyko.',
    'select_images_first' => 'Prašome pirmiausia pasirinkti nuotraukas.',
    'no_images_selected' => 'Nepasirinkta jokių nuotraukų.',
    'confirm_bulk_delete' => 'Ar tikrai norite ištrinti pasirinktas nuotraukas?',
    'confirm_bulk_activate' => 'Ar tikrai norite aktyvuoti pasirinktas nuotraukas?',
    'confirm_bulk_deactivate' => 'Ar tikrai norite deaktyvuoti pasirinktas nuotraukas?',

    // Image Editor
    'edit_image' => 'Redaguoti Nuotrauką',
    'crop_image' => 'Apkarpyti Nuotrauką',
    'resize_image' => 'Keisti Nuotraukos Dydį',
    'rotate_image' => 'Pasukti Nuotrauką',
    'flip_image' => 'Apversti Nuotrauką',
    'adjust_brightness' => 'Koreguoti Šviesumą',
    'adjust_contrast' => 'Koreguoti Kontrastą',
    'apply_filters' => 'Taikyti Filtrus',
    'reset_changes' => 'Atkurti Pakeitimus',
    'save_changes' => 'Išsaugoti Pakeitimus',
    'cancel_changes' => 'Atšaukti Pakeitimus',

    // Image Gallery
    'image_gallery' => 'Nuotraukų Galerija',
    'view_full_size' => 'Žiūrėti Pilną Dydį',
    'download_image' => 'Atsisiųsti Nuotrauką',
    'copy_image_url' => 'Kopijuoti Nuotraukos URL',
    'share_image' => 'Dalintis Nuotrauka',
    'print_image' => 'Spausdinti Nuotrauką',

    // Search and Filter
    'search_images' => 'Ieškoti Nuotraukų',
    'filter_by_variant' => 'Filtruoti pagal Variantą',
    'filter_by_status' => 'Filtruoti pagal Būseną',
    'filter_by_date' => 'Filtruoti pagal Datą',
    'clear_filters' => 'Išvalyti Filtrus',
    'apply_filters' => 'Taikyti Filtrus',

    // Export and Import
    'export_images' => 'Eksportuoti Nuotraukas',
    'import_images' => 'Importuoti Nuotraukas',
    'export_selected' => 'Eksportuoti Pasirinktas',
    'import_from_file' => 'Importuoti iš Failo',
    'import_from_url' => 'Importuoti iš URL',
    'import_progress' => 'Importo Eiga',
    'import_completed' => 'Importas baigtas sėkmingai.',
    'import_failed' => 'Importas nepavyko.',

    // Permissions
    'view_variant_images' => 'Žiūrėti Varianto Nuotraukas',
    'create_variant_images' => 'Kurti Varianto Nuotraukas',
    'edit_variant_images' => 'Redaguoti Varianto Nuotraukas',
    'delete_variant_images' => 'Trinti Varianto Nuotraukas',
    'manage_variant_images' => 'Valdyti Varianto Nuotraukas',

    // Audit Log
    'image_created' => 'Varianto nuotrauka sukurta',
    'image_updated' => 'Varianto nuotrauka atnaujinta',
    'image_deleted' => 'Varianto nuotrauka ištrinta',
    'image_activated' => 'Varianto nuotrauka aktyvuota',
    'image_deactivated' => 'Varianto nuotrauka deaktyvuota',
    'image_set_primary' => 'Varianto nuotrauka nustatyta kaip pagrindinė',
    'image_duplicated' => 'Varianto nuotrauka nukopijuota',
    'images_reordered' => 'Varianto nuotraukos perrikiuotos',
    'bulk_operation_performed' => 'Atlikta masinė operacija su varianto nuotraukomis',
];
