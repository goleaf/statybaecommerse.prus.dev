<?php declare(strict_types=1);

return [
    'login' => [
        'heading' => 'Sign in to your account',
        'form' => [
            'email' => [
                'label' => 'Email address',
            ],
            'password' => [
                'label' => 'Password',
            ],
            'remember' => [
                'label' => 'Remember me',
            ],
            'actions' => [
                'authenticate' => [
                    'label' => 'Sign in',
                ],
            ],
        ],
        'messages' => [
            'failed' => 'These credentials do not match our records.',
        ],
    ],
    'password_reset' => [
        'request' => [
            'heading' => 'Forgot your password?',
            'form' => [
                'email' => [
                    'label' => 'Email address',
                ],
                'actions' => [
                    'request' => [
                        'label' => 'Send link',
                    ],
                ],
            ],
            'messages' => [
                'throttled' => 'Too many attempts. Please try again in :seconds seconds.',
            ],
            'notifications' => [
                'throttled' => [
                    'title' => 'Too many attempts',
                    'body' => 'Please try again in :seconds seconds.',
                ],
            ],
        ],
        'reset' => [
            'heading' => 'Reset your password',
            'form' => [
                'email' => [
                    'label' => 'Email address',
                ],
                'password' => [
                    'label' => 'Password',
                    'validation_attribute' => 'password',
                ],
                'password_confirmation' => [
                    'label' => 'Confirm password',
                ],
                'actions' => [
                    'reset' => [
                        'label' => 'Reset password',
                    ],
                ],
            ],
            'messages' => [
                'throttled' => 'Too many attempts. Please try again in :seconds seconds.',
            ],
            'notifications' => [
                'throttled' => [
                    'title' => 'Too many attempts',
                    'body' => 'Please try again in :seconds seconds.',
                ],
            ],
        ],
    ],
    'pages' => [
        'health_check' => [
            'title' => 'Health Check',
            'heading' => 'Health Check',
            'navigation_label' => 'Health Check',
        ],
    ],
    'widgets' => [
        'account' => [
            'widget' => [
                'actions' => [
                    'open_user_menu' => [
                        'label' => 'User menu',
                    ],
                ],
            ],
        ],
        'filament_info' => [
            'actions' => [
                'open_documentation' => [
                    'label' => 'Open documentation',
                ],
                'open_github' => [
                    'label' => 'Open GitHub',
                ],
            ],
        ],
    ],
    'layout' => [
        'actions' => [
            'sidebar' => [
                'collapse' => [
                    'label' => 'Collapse sidebar',
                ],
                'expand' => [
                    'label' => 'Expand sidebar',
                ],
            ],
            'theme_switcher' => [
                'dark' => [
                    'label' => 'Dark mode',
                ],
                'light' => [
                    'label' => 'Light mode',
                ],
                'system' => [
                    'label' => 'System mode',
                ],
            ],
            'logout' => [
                'label' => 'Sign out',
            ],
        ],
    ],
    'pages' => [
        'dashboard' => [
            'title' => 'Dashboard',
            'heading' => 'Dashboard',
            'navigation_label' => 'Dashboard',
        ],
    ],
    'resources' => [
        'label' => 'Resource',
        'plural_label' => 'Resources',
        'navigation_label' => 'Resources',
        'navigation_group' => 'Resources',
        'pages' => [
            'create' => [
                'title' => 'Create :label',
                'heading' => 'Create :label',
                'breadcrumb' => 'Create',
                'form' => [
                    'actions' => [
                        'create' => [
                            'label' => 'Create',
                        ],
                        'create_another' => [
                            'label' => 'Create & create another',
                        ],
                        'cancel' => [
                            'label' => 'Cancel',
                        ],
                    ],
                ],
            ],
            'edit' => [
                'title' => 'Edit :label',
                'heading' => 'Edit :label',
                'breadcrumb' => 'Edit',
                'form' => [
                    'actions' => [
                        'save' => [
                            'label' => 'Save changes',
                        ],
                        'cancel' => [
                            'label' => 'Cancel',
                        ],
                    ],
                ],
            ],
            'list' => [
                'title' => ':label',
                'heading' => ':label',
                'breadcrumb' => 'List',
            ],
        ],
    ],
];
