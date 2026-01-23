<?php

declare(strict_types=1);

return [
    // Deprecated audit logging
    'audit_logging' => [
        'feature_removed'     => 'Senasis audito žurnalo vedimas pašalintas',
        'security_maintained' => 'Saugumo žurnalo vedimas tęsiamas per Laravel žurnalus',
        'compliance_note'     => 'Atitikties reikalavimai tenkinami per standartinį žurnalo vedimą',
    ],

    // General deprecation messages
    'general' => [
        'feature_deprecated'     => 'Ši funkcija pasenusi',
        'backward_compatibility' => 'Atgalinis suderinamumas išlaikomas',
        'safe_defaults'          => 'Pateikiamos saugios numatytosios reikšmės',
        'no_data_loss'           => 'Jokie esami duomenys neprarasti',
    ],

    // User-facing messages
    'messages' => [
        'functionality_unavailable' => 'Ši funkcija šiuo metu nepasiekiama',
        'feature_being_updated'     => 'Ši funkcija atnaujinama',
        'check_back_later'          => 'Patikrinkite vėliau dėl atnaujinimų',
    ],
];
