<?php

declare(strict_types=1);

return [
    'plural' => 'Валюты',
    'single' => 'Валюта',

    'basic_information' => 'Основная информация',
    'name'              => 'Название',
    'code'              => 'Код валюты',
    'code_help'         => 'Используйте трехбуквенный код ISO валюты, например EUR или USD.',
    'symbol'            => 'Символ',
    'symbol_help'       => 'Знак, отображаемый рядом с ценами, например €, $ или £.',
    'iso_code'          => 'ISO идентификатор',
    'iso_code_help'     => 'Необязательный расширенный ISO или банковский идентификатор для внутреннего учета.',
    'description'       => 'Описание',

    'exchange_rates'     => 'Курсы обмена',
    'exchange_rate'      => 'Курс обмена',
    'exchange_rate_help' => 'Укажите курс относительно выбранной базовой валюты.',
    'base_currency'      => 'Базовая валюта',
    'base_currency_help' => 'Валюта, используемая в качестве точки отсчета для конвертаций.',

    'formatting'      => 'Форматирование',
    'decimal_places'  => 'Знаков после запятой',
    'symbol_position' => 'Положение символа',
    'positions'       => [
        'before' => 'Перед суммой',
        'after'  => 'После суммы',
    ],
    'thousands_separator'      => 'Разделитель тысяч',
    'thousands_separator_help' => 'Символ, который разделяет тысячи, например запятая или пробел.',
    'decimal_separator'        => 'Десятичный разделитель',
    'decimal_separator_help'   => 'Символ, который разделяет дробную часть, например точка или запятая.',

    'settings'         => 'Настройки',
    'is_active'        => 'Активна',
    'is_default'       => 'Валюта по умолчанию',
    'sort_order'       => 'Порядок сортировки',
    'auto_update_rate' => 'Автообновление курса',

    'created_at' => 'Создано',
    'updated_at' => 'Обновлено',

    'active_only'        => 'Только активные',
    'inactive_only'      => 'Только неактивные',
    'default_only'       => 'Только по умолчанию',
    'non_default_only'   => 'Только не по умолчанию',
    'auto_update_only'   => 'Только автообновление',
    'manual_update_only' => 'Только ручное обновление',

    'deactivate'                  => 'Отключить',
    'activate'                    => 'Включить',
    'activated_successfully'      => 'Валюта успешно включена.',
    'deactivated_successfully'    => 'Валюта успешно отключена.',
    'set_default'                 => 'Сделать основной',
    'set_as_default_successfully' => 'Валюта успешно назначена основной.',
    'update_rate'                 => 'Обновить курс',
    'rate_updated_successfully'   => 'Курс валюты успешно обновлён.',
    'rate_update_failed'          => 'Не удалось обновить курс валюты.',

    'activate_selected'          => 'Включить выбранные',
    'deactivate_selected'        => 'Отключить выбранные',
    'bulk_activated_success'     => 'Выбранные валюты успешно включены.',
    'bulk_deactivated_success'   => 'Выбранные валюты успешно отключены.',
    'update_rates'               => 'Обновить курсы',
    'rates_updated_successfully' => 'Курсы обмена успешно обновлены.',
    'rates_update_failed'        => 'Не удалось обновить ни один курс. Проверьте настройки провайдера.',
];
