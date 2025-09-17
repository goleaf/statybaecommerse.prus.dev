<?php

declare(strict_types=1);

return [
    'login' => [
        'heading' => 'Prisijunkite prie savo paskyros',
        'form' => [
            'email' => [
                'label' => 'El. paštas',
            ],
            'password' => [
                'label' => 'Slaptažodis',
            ],
            'remember' => [
                'label' => 'Prisiminti mane',
            ],
            'actions' => [
                'authenticate' => [
                    'label' => 'Prisijungti',
                ],
            ],
        ],
        'messages' => [
            'failed' => 'Neteisingi prisijungimo duomenys.',
        ],
    ],
    'password_reset' => [
        'request' => [
            'heading' => 'Pamiršote slaptažodį?',
            'form' => [
                'email' => [
                    'label' => 'El. paštas',
                ],
                'actions' => [
                    'request' => [
                        'label' => 'Siųsti nuorodą',
                    ],
                ],
            ],
            'messages' => [
                'throttled' => 'Per daug bandymų. Bandykite dar kartą po :seconds sekundžių.',
            ],
            'notifications' => [
                'throttled' => [
                    'title' => 'Per daug bandymų',
                    'body' => 'Bandykite dar kartą po :seconds sekundžių.',
                ],
            ],
        ],
        'reset' => [
            'heading' => 'Atstatyti slaptažodį',
            'form' => [
                'email' => [
                    'label' => 'El. paštas',
                ],
                'password' => [
                    'label' => 'Slaptažodis',
                    'validation_attribute' => 'slaptažodis',
                ],
                'password_confirmation' => [
                    'label' => 'Patvirtinti slaptažodį',
                ],
                'actions' => [
                    'reset' => [
                        'label' => 'Atstatyti slaptažodį',
                    ],
                ],
            ],
            'messages' => [
                'throttled' => 'Per daug bandymų. Bandykite dar kartą po :seconds sekundžių.',
            ],
            'notifications' => [
                'throttled' => [
                    'title' => 'Per daug bandymų',
                    'body' => 'Bandykite dar kartą po :seconds sekundžių.',
                ],
            ],
        ],
    ],
    'pages' => [
        'health_check' => [
            'title' => 'Sistemos būklė',
            'heading' => 'Sistemos būklė',
            'navigation_label' => 'Sistemos būklė',
        ],
    ],
    'widgets' => [
        'account' => [
            'widget' => [
                'actions' => [
                    'open_user_menu' => [
                        'label' => 'Naudotojo meniu',
                    ],
                ],
            ],
        ],
        'filament_info' => [
            'actions' => [
                'open_documentation' => [
                    'label' => 'Atidaryti dokumentaciją',
                ],
                'open_github' => [
                    'label' => 'Atidaryti GitHub',
                ],
            ],
        ],
    ],
    'layout' => [
        'actions' => [
            'sidebar' => [
                'collapse' => [
                    'label' => 'Suskleisti šoninį meniu',
                ],
                'expand' => [
                    'label' => 'Išskleisti šoninį meniu',
                ],
            ],
            'theme_switcher' => [
                'dark' => [
                    'label' => 'Tamsus režimas',
                ],
                'light' => [
                    'label' => 'Šviesus režimas',
                ],
                'system' => [
                    'label' => 'Sistemos režimas',
                ],
            ],
            'logout' => [
                'label' => 'Atsijungti',
            ],
        ],
    ],
    'pages' => [
        'dashboard' => [
            'title' => 'Valdymo skydas',
            'heading' => 'Valdymo skydas',
            'navigation_label' => 'Valdymo skydas',
        ],
    ],
    'resources' => [
        'label' => 'Ištekliai',
        'plural_label' => 'Ištekliai',
        'navigation_label' => 'Ištekliai',
        'navigation_group' => 'Ištekliai',
        'pages' => [
            'create' => [
                'title' => 'Sukurti :label',
                'heading' => 'Sukurti :label',
                'breadcrumb' => 'Sukurti',
                'form' => [
                    'actions' => [
                        'create' => [
                            'label' => 'Sukurti',
                        ],
                        'create_another' => [
                            'label' => 'Sukurti ir sukurti kitą',
                        ],
                        'cancel' => [
                            'label' => 'Atšaukti',
                        ],
                    ],
                ],
            ],
            'edit' => [
                'title' => 'Redaguoti :label',
                'heading' => 'Redaguoti :label',
                'breadcrumb' => 'Redaguoti',
                'form' => [
                    'actions' => [
                        'save' => [
                            'label' => 'Išsaugoti pakeitimus',
                        ],
                        'cancel' => [
                            'label' => 'Atšaukti',
                        ],
                    ],
                ],
            ],
            'list' => [
                'title' => ':label',
                'heading' => ':label',
                'breadcrumb' => 'Sąrašas',
            ],
        ],
    ],
];
