<?php

declare(strict_types=1);

return [
    // Actions
    'actions' => [
        'mark_as_read'         => 'Pažymėti kaip perskaitytą',
        'mark_as_unread'       => 'Pažymėti kaip neperskaitytą',
        'mark_all_read'        => 'Pažymėti visus kaip perskaitytus',
        'delete_notification'  => 'Ištrinti pranešimą',
        'clear_all'            => 'Išvalyti visus',
        'refresh'              => 'Atnaujinti',
        'filter_notifications' => 'Filtruoti pranešimus',
    ],

    // Filters
    'filters' => [
        'all'         => 'Visi pranešimai',
        'unread_only' => 'Tik neperskaityti',
        'by_type'     => 'Pagal tipą',
        'recent'      => 'Paskutiniai',
        'urgent'      => 'Skubūs',
    ],

    // Types
    'types' => [
        'order'      => 'Užsakymai',
        'product'    => 'Produktai',
        'user'       => 'Vartotojai',
        'system'     => 'Sistema',
        'payment'    => 'Mokėjimai',
        'shipping'   => 'Pristatymas',
        'review'     => 'Atsiliepimai',
        'promotion'  => 'Akcijos',
        'newsletter' => 'Naujienlaiškis',
        'support'    => 'Pagalba',
    ],

    // Messages
    'messages' => [
        'no_notifications'     => 'Pranešimų nėra',
        'all_read'             => 'Visi pranešimai pažymėti kaip perskaityti',
        'notification_deleted' => 'Pranešimas ištrintas',
        'all_cleared'          => 'Visi pranešimai išvalyti',
        'loading'              => 'Kraunami pranešimai...',
        'error_loading'        => 'Klaida kraunant pranešimus',
        'mark_read_success'    => 'Pranešimas pažymėtas kaip perskaitytas',
        'mark_unread_success'  => 'Pranešimas pažymėtas kaip neperskaitytas',
    ],

    // Errors
    'errors' => [
        'unauthorized'  => 'Neturite teisės peržiūrėti pranešimų',
        'not_found'     => 'Pranešimas nerastas',
        'action_failed' => 'Veiksmas nepavyko',
        'rate_limit'    => 'Per daug bandymų. Pabandykite vėliau',
    ],

    // Labels
    'labels' => [
        'notification_center' => 'Pranešimų centras',
        'unread_count'        => 'Neperskaitytų skaičius',
        'created_at'          => 'Sukurta',
        'read_at'             => 'Perskaityta',
        'urgent'              => 'Skubus',
        'normal'              => 'Normalus',
        'priority'            => 'Prioritetas',
    ],

    // Accessibility
    'aria' => [
        'notification_list'         => 'Pranešimų sąrašas',
        'mark_as_read'              => 'Pažymėti pranešimą kaip perskaitytą',
        'mark_as_unread'            => 'Pažymėti pranešimą kaip neperskaitytą',
        'delete_notification'       => 'Ištrinti pranešimą',
        'notification_urgent'       => 'Skubus pranešimas',
        'notification_read'         => 'Perskaitytas pranešimas',
        'notification_unread'       => 'Neperskaitytas pranešimas',
        'filter_by_type'            => 'Filtruoti pagal tipą',
        'close_notification_center' => 'Uždaryti pranešimų centrą',
    ],

    // Pagination
    'pagination' => [
        'showing'   => 'Rodoma',
        'of'        => 'iš',
        'results'   => 'rezultatų',
        'per_page'  => 'puslapyje',
        'load_more' => 'Krauti daugiau',
        'no_more'   => 'Daugiau pranešimų nėra',
    ],
];
