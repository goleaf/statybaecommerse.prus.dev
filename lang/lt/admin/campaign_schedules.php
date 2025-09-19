<?php

return [
    'title' => 'Kampanijų grafikai',
    'plural' => 'Kampanijų grafikai',
    'single' => 'Kampanijos grafikas',
    'form' => [
        'tabs' => [
            'basic_information' => 'Pagrindinė informacija',
            'schedule_config' => 'Grafiko konfigūracija',
            'campaign_details' => 'Kampanijos detalės',
        ],
        'sections' => [
            'basic_information' => 'Pagrindinė informacija',
            'schedule_config' => 'Grafiko konfigūracija',
            'campaign_details' => 'Kampanijos detalės',
        ],
        'fields' => [
            'campaign' => 'Kampanija',
            'schedule_type' => 'Grafiko tipas',
            'next_run_at' => 'Kitas paleidimas',
            'last_run_at' => 'Paskutinis paleidimas',
            'is_active' => 'Aktyvus',
            'schedule_config' => 'Grafiko konfigūracija',
            'config_key' => 'Konfigūracijos raktas',
            'config_value' => 'Konfigūracijos reikšmė',
            'campaign_name' => 'Kampanijos pavadinimas',
            'campaign_status' => 'Kampanijos būsena',
            'campaign_type' => 'Kampanijos tipas',
            'schedule_status' => 'Grafiko būsena',
        ],
    ],
    'schedule_types' => [
        'once' => 'Vieną kartą',
        'daily' => 'Kasdien',
        'weekly' => 'Kas savaitę',
        'monthly' => 'Kas mėnesį',
        'custom' => 'Pasirinktinis',
    ],
    'status' => [
        'active' => 'Aktyvus',
        'inactive' => 'Neaktyvus',
        'scheduled' => 'Suplanuotas',
        'ready' => 'Paruoštas',
    ],
    'filters' => [
        'campaign' => 'Kampanija',
        'schedule_type' => 'Grafiko tipas',
        'is_active' => 'Aktyvus',
        'next_run_at' => 'Kitas paleidimas',
        'last_run_at' => 'Paskutinis paleidimas',
        'overdue' => 'Pavėluotas',
    ],
    'actions' => [
        'activate' => 'Aktyvuoti',
        'deactivate' => 'Deaktyvuoti',
        'run_now' => 'Paleisti dabar',
        'activate_bulk' => 'Aktyvuoti masiniškai',
        'deactivate_bulk' => 'Deaktyvuoti masiniškai',
    ],
    'notifications' => [
        'activated_successfully' => 'Sėkmingai aktyvuotas',
        'deactivated_successfully' => 'Sėkmingai deaktyvuotas',
        'run_successfully' => 'Sėkmingai paleistas',
        'bulk_activated_successfully' => 'Sėkmingai aktyvuoti masiniškai',
        'bulk_deactivated_successfully' => 'Sėkmingai deaktyvuoti masiniškai',
    ],
];
