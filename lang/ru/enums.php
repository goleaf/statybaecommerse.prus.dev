<?php

declare(strict_types=1);

return [
    'industry' => [
        'construction'  => 'Строительство',
        'education'     => 'Образование',
        'finance'       => 'Финансы',
        'healthcare'    => 'Здравоохранение',
        'manufacturing' => 'Производство',
        'other'         => 'Другое',
        'retail'        => 'Розничная торговля',
        'technology'    => 'Технологии',
    ],
    'organization_type' => [
        'company'    => 'Компания',
        'department' => 'Отдел',
        'team'       => 'Команда',
    ],
    'payment_method' => [
        'apple_pay'        => 'Apple Pay',
        'bank_transfer'    => 'Банковский перевод',
        'cash_on_delivery' => 'Наложенный платеж',
        'credit_card'      => 'Кредитная карта',
        'google_pay'       => 'Google Pay',
        'paypal'           => 'PayPal',
        'stripe'           => 'Stripe',
    ],
    'payment_status' => [
        'authorized'         => 'Авторизован',
        'captured'           => 'Списан',
        'failed'             => 'Ошибка',
        'paid'               => 'Оплачен',
        'partially_refunded' => 'Частично возвращен',
        'pending'            => 'В ожидании',
        'refunded'           => 'Возвращен',
        'settled'            => 'Зачислен',
    ],
    'priority' => 'enums.priority',
    'status'   => 'enums.status',
];
