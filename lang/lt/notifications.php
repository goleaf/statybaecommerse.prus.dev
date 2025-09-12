<?php

return [
    // General notifications
    'no_notifications' => 'Nėra pranešimų',
    'check_later' => 'Patikrinkite vėliau.',
    'mark_as_read' => 'Pažymėti kaip perskaitytą',
    'mark_all_as_read' => 'Pažymėti visus kaip perskaitytus',
    'delete_notification' => 'Ištrinti pranešimą',
    'delete_all_notifications' => 'Ištrinti visus pranešimus',
    'notification_deleted' => 'Pranešimas ištrintas',
    'all_notifications_deleted' => 'Visi pranešimai ištrinti',
    'all_marked_as_read' => 'Visi pranešimai pažymėti kaip perskaityti',
    
    // Notification types
    'types' => [
        'info' => 'Informacija',
        'success' => 'Sėkmė',
        'warning' => 'Įspėjimas',
        'error' => 'Klaida',
        'order' => 'Užsakymas',
        'product' => 'Prekė',
        'user' => 'Vartotojas',
        'system' => 'Sistema',
        'payment' => 'Mokėjimas',
        'shipping' => 'Pristatymas',
        'review' => 'Atsiliepimas',
        'promotion' => 'Akcija',
        'newsletter' => 'Naujienlaiškis',
        'support' => 'Palaikymas',
    ],
    
    // Order notifications
    'order' => [
        'created' => 'Naujas užsakymas sukurtas',
        'updated' => 'Užsakymas atnaujintas',
        'cancelled' => 'Užsakymas atšauktas',
        'completed' => 'Užsakymas užbaigtas',
        'shipped' => 'Užsakymas išsiųstas',
        'delivered' => 'Užsakymas pristatytas',
        'payment_received' => 'Mokėjimas gautas',
        'payment_failed' => 'Mokėjimas nepavyko',
        'refund_processed' => 'Grąžinimas apdorotas',
    ],
    
    // Product notifications
    'product' => [
        'created' => 'Nauja prekė sukurta',
        'updated' => 'Prekė atnaujinta',
        'deleted' => 'Prekė ištrinta',
        'low_stock' => 'Mažas prekių kiekis',
        'out_of_stock' => 'Prekė išparduota',
        'back_in_stock' => 'Prekė vėl turima',
        'price_changed' => 'Prekės kaina pakeista',
        'review_added' => 'Pridėtas atsiliepimas',
    ],
    
    // User notifications
    'user' => [
        'registered' => 'Naujas vartotojas užsiregistravo',
        'profile_updated' => 'Profilis atnaujintas',
        'password_changed' => 'Slaptažodis pakeistas',
        'email_verified' => 'El. paštas patvirtintas',
        'login' => 'Prisijungimas',
        'logout' => 'Atsijungimas',
        'account_suspended' => 'Paskyra sustabdyta',
        'account_activated' => 'Paskyra aktyvuota',
    ],
    
    // System notifications
    'system' => [
        'maintenance_started' => 'Priežiūros darbai pradėti',
        'maintenance_completed' => 'Priežiūros darbai baigti',
        'backup_created' => 'Atsarginė kopija sukurta',
        'update_available' => 'Galimas atnaujinimas',
        'security_alert' => 'Saugumo įspėjimas',
        'performance_issue' => 'Veikimo problema',
    ],
    
    // Payment notifications
    'payment' => [
        'processed' => 'Mokėjimas apdorotas',
        'failed' => 'Mokėjimas nepavyko',
        'refunded' => 'Mokėjimas grąžintas',
        'disputed' => 'Mokėjimas ginčijamas',
        'chargeback' => 'Mokėjimo grąžinimas',
    ],
    
    // Shipping notifications
    'shipping' => [
        'label_created' => 'Siuntimo etiketė sukurta',
        'picked_up' => 'Siunta paimta',
        'in_transit' => 'Siunta kelyje',
        'out_for_delivery' => 'Siunta pristatoma',
        'delivered' => 'Siunta pristatyta',
        'delivery_failed' => 'Pristatymas nepavyko',
        'returned' => 'Siunta grąžinta',
    ],
    
    // Review notifications
    'review' => [
        'submitted' => 'Atsiliepimas pateiktas',
        'approved' => 'Atsiliepimas patvirtintas',
        'rejected' => 'Atsiliepimas atmestas',
        'replied' => 'Atsakyta į atsiliepimą',
    ],
    
    // Promotion notifications
    'promotion' => [
        'created' => 'Nauja akcija sukurta',
        'started' => 'Akcija pradėta',
        'ended' => 'Akcija baigta',
        'expiring_soon' => 'Akcija greitai baigsis',
    ],
    
    // Newsletter notifications
    'newsletter' => [
        'subscribed' => 'Prenumeruota naujienlaiškis',
        'unsubscribed' => 'Atsisakyta naujienlaiškio',
        'sent' => 'Naujienlaiškis išsiųstas',
    ],
    
    // Support notifications
    'support' => [
        'ticket_created' => 'Bilietas sukurta',
        'ticket_updated' => 'Bilietas atnaujintas',
        'ticket_closed' => 'Bilietas uždarytas',
        'message_received' => 'Gautas pranešimas',
        'response_sent' => 'Atsakymas išsiųstas',
    ],
    
    // Time formats
    'time' => [
        'just_now' => 'ką tik',
        'minutes_ago' => 'prieš :count min.',
        'hours_ago' => 'prieš :count val.',
        'days_ago' => 'prieš :count d.',
        'weeks_ago' => 'prieš :count sav.',
        'months_ago' => 'prieš :count mėn.',
        'years_ago' => 'prieš :count m.',
    ],
];
