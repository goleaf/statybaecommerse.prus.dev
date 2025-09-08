<?php declare(strict_types=1);

return [
    // Navigation and Labels
    'enhanced_settings' => 'Išplėstiniai Nustatymai',
    'enhanced_setting' => 'Išplėstinis Nustatymas',
    'settings' => 'Nustatymai',
    'setting' => 'Nustatymas',
    // Form Fields
    'group' => 'Grupė',
    'key' => 'Raktas',
    'value' => 'Reikšmė',
    'type' => 'Tipas',
    'description' => 'Aprašymas',
    'is_public' => 'Viešas',
    'is_encrypted' => 'Šifruotas',
    'validation_rules' => 'Validacijos Taisyklės',
    'sort_order' => 'Rūšiavimo Tvarka',
    // Groups
    'groups' => [
        'general' => 'Bendri',
        'ecommerce' => 'El. prekyba',
        'email' => 'El. paštas',
        'payment' => 'Mokėjimai',
        'shipping' => 'Pristatymas',
        'seo' => 'SEO',
        'security' => 'Saugumas',
        'api' => 'API',
        'appearance' => 'Išvaizda',
        'notifications' => 'Pranešimai',
    ],
    // Types
    'types' => [
        'text' => 'Tekstas',
        'textarea' => 'Teksto sritis',
        'number' => 'Skaičius',
        'boolean' => 'Loginis',
        'json' => 'JSON',
        'array' => 'Masyvas',
        'select' => 'Pasirinkimas',
        'file' => 'Failas',
        'color' => 'Spalva',
        'date' => 'Data',
        'datetime' => 'Data ir laikas',
    ],
    // Form Sections
    'setting_information' => 'Nustatymo Informacija',
    'value_configuration' => 'Reikšmės Konfigūracija',
    'advanced_options' => 'Išplėstiniai Parametrai',
    // Help Text
    'help' => [
        'key' => 'Naudokite tik mažąsias raides, skaičius, pabraukimus ir taškus',
        'type' => 'Šio nustatymo duomenų tipas',
        'is_public' => 'Viešus nustatymus galima pasiekti iš svetainės priekinės dalies',
        'is_encrypted' => 'Jautrūs nustatymai bus šifruojami duomenų bazėje',
        'validation_rules' => 'Laravel validacijos taisyklės raktas-reikšmė formatu',
        'json_format' => 'Įveskite galiojantį JSON formatą',
        'group_description' => 'Grupė, kuriai priklauso šis nustatymas (pvz., "bendri", "el_pastas", "mokejimas")',
    ],
    // Field Labels
    'labels' => [
        'json_value' => 'JSON Reikšmė',
        'color_value' => 'Spalvos Reikšmė',
        'date_value' => 'Datos Reikšmė',
        'datetime_value' => 'Datos ir Laiko Reikšmė',
        'public' => 'Viešas',
        'encrypted' => 'Šifruotas',
        'last_updated' => 'Paskutinį Kartą Atnaujinta',
        'updated_at' => 'Atnaujinta',
    ],
    // Actions
    'actions' => [
        'create' => 'Sukurti Nustatymą',
        'edit' => 'Redaguoti Nustatymą',
        'delete' => 'Ištrinti Nustatymą',
        'view' => 'Peržiūrėti Nustatymą',
        'save' => 'Išsaugoti Nustatymą',
        'cancel' => 'Atšaukti',
        'back' => 'Grįžti į Nustatymus',
    ],
    // Messages
    'messages' => [
        'created' => 'Nustatymas sėkmingai sukurtas',
        'updated' => 'Nustatymas sėkmingai atnaujintas',
        'deleted' => 'Nustatymas sėkmingai ištrintas',
        'not_found' => 'Nustatymas nerastas',
        'validation_failed' => 'Validacija nepavyko',
        'key_exists' => 'Nustatymas su šiuo raktu jau egzistuoja',
        'invalid_json' => 'Neteisingas JSON formatas',
        'encryption_failed' => 'Nepavyko šifruoti nustatymo reikšmės',
        'decryption_failed' => 'Nepavyko iššifruoti nustatymo reikšmės',
    ],
    // Filters
    'filters' => [
        'all_groups' => 'Visos Grupės',
        'all_types' => 'Visi Tipai',
        'public_only' => 'Tik Viešus',
        'private_only' => 'Tik Privačius',
        'encrypted_only' => 'Tik Šifruotus',
        'non_encrypted' => 'Nešifruotus',
    ],
    // Table Headers
    'table' => [
        'group' => 'Grupė',
        'key' => 'Raktas',
        'type' => 'Tipas',
        'value' => 'Reikšmė',
        'public' => 'Viešas',
        'encrypted' => 'Šifruotas',
        'updated_at' => 'Atnaujinta',
        'actions' => 'Veiksmai',
    ],
    // Validation Messages
    'validation' => [
        'key_required' => 'Nustatymo raktas yra privalomas',
        'key_unique' => 'Nustatymo raktas turi būti unikalus',
        'key_format' => 'Nustatymo raktas gali turėti tik mažąsias raides, skaičius, pabraukimus ir taškus',
        'value_required' => 'Nustatymo reikšmė yra privaloma',
        'type_required' => 'Nustatymo tipas yra privalomas',
        'group_required' => 'Nustatymo grupė yra privaloma',
        'sort_order_numeric' => 'Rūšiavimo tvarka turi būti skaičius',
        'json_valid' => 'Reikšmė turi būti galiojantis JSON formatas',
    ],
    // Tooltips
    'tooltips' => [
        'public_setting' => 'Šis nustatymas gali būti pasiekiamas iš svetainės priekinės dalies',
        'encrypted_setting' => 'Šio nustatymo reikšmė yra šifruojama duomenų bazėje',
        'json_setting' => 'Šis nustatymas saugo JSON duomenis',
        'required_setting' => 'Šis nustatymas yra būtinas sistemos veikimui',
        'copy_key' => 'Spustelėkite, kad nukopijuotumėte nustatymo raktą',
        'edit_setting' => 'Redaguoti šį nustatymą',
        'delete_setting' => 'Ištrinti šį nustatymą',
        'view_setting' => 'Peržiūrėti nustatymo detales',
    ],
    // Placeholders
    'placeholders' => [
        'key' => 'pvz., svetaines_pavadinimas, programos_versija',
        'value' => 'Įveskite nustatymo reikšmę',
        'description' => 'Aprašykite, ką kontroliuoja šis nustatymas',
        'json_value' => '{"raktas": "reikšmė", "masyvas": [1, 2, 3]}',
        'validation_rules' => 'required|string|max:255',
        'sort_order' => '0',
    ],
    // Empty States
    'empty_states' => [
        'no_settings' => 'Nustatymų nerasta',
        'no_settings_description' => 'Sukurkite pirmą nustatymą, kad pradėtumėte',
        'no_results' => 'Nėra nustatymų, atitinkančių jūsų paieškos kriterijus',
        'no_results_description' => 'Pabandykite koreguoti filtrus arba paieškos terminus',
    ],
    // Bulk Actions
    'bulk_actions' => [
        'delete_selected' => 'Ištrinti Pasirinktus',
        'export_selected' => 'Eksportuoti Pasirinktus',
        'make_public' => 'Padaryti Viešus',
        'make_private' => 'Padaryti Privačius',
        'encrypt_selected' => 'Šifruoti Pasirinktus',
        'decrypt_selected' => 'Iššifruoti Pasirinktus',
    ],
    // Import/Export
    'import_export' => [
        'import' => 'Importuoti Nustatymus',
        'export' => 'Eksportuoti Nustatymus',
        'import_success' => 'Nustatymai sėkmingai importuoti',
        'export_success' => 'Nustatymai sėkmingai eksportuoti',
        'import_failed' => 'Nepavyko importuoti nustatymų',
        'export_failed' => 'Nepavyko eksportuoti nustatymų',
        'invalid_file' => 'Neteisingas failo formatas',
        'file_too_large' => 'Failas per didelis',
    ],
];
