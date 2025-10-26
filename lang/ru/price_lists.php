<?php

declare(strict_types=1);

return [
    'title'  => 'Прайс-листы',
    'plural' => 'Прайс-листы',
    'single' => 'Прайс-лист',

    // Sections
    'basic_information' => 'Основная информация',
    'availability'      => 'Доступность и условия',
    'settings'          => 'Настройки',

    // Fields
    'name'             => 'Название',
    'code'             => 'Код',
    'currency'         => 'Валюта',
    'priority'         => 'Приоритет',
    'description'      => 'Описание',
    'is_enabled'       => 'Включён',
    'is_default'       => 'По умолчанию',
    'auto_apply'       => 'Автоприменение',
    'starts_at'        => 'Дата начала',
    'ends_at'          => 'Дата окончания',
    'starts_at_from'   => 'Дата начала с',
    'starts_at_until'  => 'Дата начала до',
    'ends_at_from'     => 'Дата окончания с',
    'ends_at_until'    => 'Дата окончания до',
    'min_order_amount' => 'Минимальная сумма заказа',
    'max_order_amount' => 'Максимальная сумма заказа',
    'created_at'       => 'Создано',
    'updated_at'       => 'Обновлено',

    // Filters & options
    'all_records'      => 'Все записи',
    'enabled_only'     => 'Только включённые',
    'disabled_only'    => 'Только отключённые',
    'default_only'     => 'Только по умолчанию',
    'non_default_only' => 'Только нестандартные',
    'auto_apply_only'  => 'Только с автоприменением',
    'manual_only'      => 'Только с ручным применением',

    // Relation data
    'customer_group'      => 'Группа клиентов',
    'discount_percentage' => 'Процент скидки',
    'is_active'           => 'Активно',
    'partner'             => 'Партнёр',
    'email'               => 'E-mail',
    'phone'               => 'Телефон',
    'commission_rate'     => 'Ставка комиссии',

    // Tabs
    'tabs' => [
        'all'        => 'Все прайс-листы',
        'active'     => 'Активные',
        'default'    => 'По умолчанию',
        'auto_apply' => 'С автоприменением',
    ],

    // Relation managers
    'relation_managers' => [
        'customer_groups' => [
            'title' => 'Группы клиентов',
        ],
        'partners' => [
            'title' => 'Партнёры',
        ],
        'items' => [
            'title' => 'Позиции прайс-листа',
        ],
    ],

    // Widgets & stats
    'stats' => [
        'total_price_lists'                  => 'Всего прайс-листов',
        'total_price_lists_description'      => 'Все прайс-листы в каталоге',
        'enabled_price_lists'                => 'Включённые прайс-листы',
        'enabled_price_lists_description'    => 'Прайс-листы, которые сейчас включены',
        'active_price_lists'                 => 'Активные прайс-листы',
        'active_price_lists_description'     => 'Прайс-листы, которые действуют в данный момент',
        'default_price_lists'                => 'Прайс-листы по умолчанию',
        'default_price_lists_description'    => 'Прайс-листы, применяемые автоматически',
        'auto_apply_price_lists'             => 'Прайс-листы с автоприменением',
        'auto_apply_price_lists_description' => 'Прайс-листы, которые назначаются клиентам автоматически',
    ],

    'charts' => [
        'activity_over_time'  => 'Активность прайс-листов во времени',
        'price_lists_created' => 'Созданные прайс-листы',
    ],
];
