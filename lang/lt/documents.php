<?php declare(strict_types=1);

return [
    // Document Types
    'invoice' => 'Sąskaita faktūra',
    'receipt' => 'Kvitas',
    'contract' => 'Sutartis',
    'agreement' => 'Susitarimas',
    'catalog' => 'Katalogas',
    'report' => 'Ataskaita',
    'certificate' => 'Sertifikatas',
    'quote' => 'Pasiūlymas',
    'proposal' => 'Projektas',
    'terms' => 'Taisyklės ir sąlygos',

    // Document Categories
    'sales' => 'Pardavimai',
    'marketing' => 'Rinkodara',
    'legal' => 'Teisiniai',
    'finance' => 'Finansai',
    'operations' => 'Operacijos',
    'customer_service' => 'Klientų aptarnavimas',
    'hr' => 'Žmogiškieji ištekliai',
    'technical' => 'Techniniai',

    // Document Status
    'draft' => 'Juodraštis',
    'published' => 'Publikuotas',
    'archived' => 'Archyvuotas',
    'pending' => 'Laukia peržiūros',

    // Document Format
    'html' => 'HTML',
    'pdf' => 'PDF',
    'docx' => 'Word dokumentas',
    'xlsx' => 'Excel skaičiuoklė',

    // Document Fields
    'document_information' => 'Dokumento informacija',
    'template_information' => 'Šablono informacija',
    'title' => 'Pavadinimas',
    'description' => 'Aprašymas',
    'content' => 'Turinys',
    'variables' => 'Kintamieji',
    'settings' => 'Nustatymai',
    'type' => 'Tipas',
    'category' => 'Kategorija',
    'status' => 'Būsena',
    'format' => 'Formatas',
    'file_path' => 'Failo kelias',
    'created_by' => 'Sukūrė',
    'generated_at' => 'Sugeneruota',
    'is_active' => 'Aktyvus',

    // Document Actions
    'generate' => 'Generuoti dokumentą',
    'generate_pdf' => 'Generuoti PDF',
    'download' => 'Atsisiųsti',
    'preview' => 'Peržiūrėti',
    'duplicate' => 'Dubliuoti',
    'archive' => 'Archyvuoti',
    'restore' => 'Atkurti',

    // Variables
    'available_variables' => 'Galimi kintamieji',
    'variable_name' => 'Kintamojo pavadinimas',
    'variable_description' => 'Aprašymas',
    'variable_value' => 'Reikšmė',
    'variable_help' => 'Naudokite kintamuosius kaip $CUSTOMER_NAME, $ORDER_TOTAL šablono turinyje',

    // Print Settings
    'print_settings' => 'Spausdinimo nustatymai',
    'page_size' => 'Puslapio dydis',
    'orientation' => 'Orientacija',
    'margins' => 'Paraštės',
    'header' => 'Antraštė',
    'footer' => 'Poraštė',
    'css' => 'Pasirinktinis CSS',

    // Page Settings
    'portrait' => 'Stačias',
    'landscape' => 'Gulsčias',
    'a4' => 'A4',
    'a3' => 'A3',
    'letter' => 'Letter',
    'legal' => 'Legal',

    // Messages
    'document_generated' => 'Dokumentas sėkmingai sugeneruotas',
    'document_deleted' => 'Dokumentas sėkmingai ištrintas',
    'template_created' => 'Šablonas sėkmingai sukurtas',
    'template_updated' => 'Šablonas sėkmingai atnaujintas',
    'template_deleted' => 'Šablonas sėkmingai ištrintas',
    'pdf_generated' => 'PDF sėkmingai sugeneruotas',
    'generation_failed' => 'Dokumento generavimas nepavyko',

    // Common Variables
    'yes' => 'Taip',
    'no' => 'Ne',
    'true' => 'Tiesa',
    'false' => 'Netiesa',
    'enabled' => 'Įjungta',
    'disabled' => 'Išjungta',

    // Help Text
    'template_help' => 'Naudokite HTML turinį su kintamaisiais kaip $CUSTOMER_NAME, $ORDER_TOTAL',
    'variables_help' => 'Apibrėžkite kintamuosius, kuriuos galima naudoti dokumentų šablonuose',
    'settings_help' => 'Konfigūruokite spausdinimo nustatymus PDF generavimui',
];