<?php

declare(strict_types=1);

return [
    'title'    => 'Статистика рекомендаций',
    'plural'   => 'Статистика рекомендаций',
    'single'   => 'Запись статистики рекомендаций',
    'sections' => [
        'basic_info'                  => 'Основная информация',
        'basic_info_description'      => 'Отслеживайте, кому принадлежит статистика и когда она собрана.',
        'referral_stats'              => 'Показатели рекомендаций',
        'referral_stats_description'  => 'Количество всех результатов рекомендаций за выбранный период.',
        'financial_stats'             => 'Финансовый эффект',
        'financial_stats_description' => 'Суммы полученных вознаграждений и предоставленных скидок.',
        'advanced'                    => 'Дополнительные сведения',
        'advanced_description'        => 'Храните дополнительные метаданные этого среза статистики.',
        'timestamps'                  => 'Временные метки',
    ],
    'fields' => [
        'user_id'               => 'Пользователь',
        'user_name'             => 'Пользователь',
        'date'                  => 'Дата',
        'total_referrals'       => 'Всего рекомендаций',
        'completed_referrals'   => 'Завершённые рекомендации',
        'pending_referrals'     => 'Ожидающие рекомендации',
        'total_rewards_earned'  => 'Полученные вознаграждения',
        'total_discounts_given' => 'Предоставленные скидки',
        'metadata'              => 'Метаданные',
        'metadata_key'          => 'Ключ',
        'metadata_value'        => 'Значение',
        'created_at'            => 'Создано',
        'updated_at'            => 'Обновлено',
    ],
    'filters' => [
        'user'          => 'Пользователь',
        'date_range'    => 'Диапазон дат',
        'from_date'     => 'С даты',
        'until_date'    => 'По дату',
        'has_referrals' => 'Есть рекомендации',
        'has_rewards'   => 'Есть вознаграждения',
    ],
    'actions' => [
        'add_metadata'      => 'Добавить метаданные',
        'refresh_stats'     => 'Обновить статистику',
        'refresh_all_stats' => 'Обновить все статистики',
    ],
    'notifications' => [
        'stats_refreshed'     => 'Статистика рекомендаций успешно обновлена.',
        'all_stats_refreshed' => 'Все статистики рекомендаций успешно обновлены.',
    ],
    'placeholders' => [
        'no_metadata' => 'Метаданные ещё не добавлены.',
    ],
];
