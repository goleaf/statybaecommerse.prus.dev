<?php

declare(strict_types=1);

use App\Support\ErrorCode;

return [
    // @translators: Displayed when a requested page or record is missing (HTTP 404).
    ErrorCode::NotFound->value => 'Страница не найдена',

    // @translators: Shown when the system encounters an unexpected failure (HTTP 500).
    ErrorCode::ServerError->value => 'Ошибка сервера',

    // @translators: Used when form submission fails validation and users must review inputs.
    ErrorCode::ValidationFailed->value => 'Проверьте введённые данные',

    // @translators: Indicates the user needs to log in before accessing the requested content.
    ErrorCode::Unauthorized->value => 'Нет доступа',

    // @translators: Indicates the user is logged in but does not have permission for the action.
    ErrorCode::Forbidden->value => 'Доступ запрещён',

    // @translators: Displayed when an order number could not be located in the system.
    ErrorCode::OrderNotFound->value => 'Заказ :order не найден.',

    // @translators: Сообщает о недостаточном количестве товара для указанного SKU.
    ErrorCodes::INVENTORY_INSUFFICIENT => 'Недостаточно запасов для артикула :sku.',
    // @translators: Показывается, когда не удалось загрузить профиль авторизованного пользователя.
    ErrorCodes::PROFILE_UNAVAILABLE => 'Профиль недоступен',
    // @translators: Показывается, когда оформление заказа прекращается из-за пустой корзины.
    ErrorCodes::CHECKOUT_CART_EMPTY => 'Корзина пуста',

    'messages' => [
        // @translators: Универсальное сообщение для API при неожиданных ошибках сервера.
        'server_error' => 'Произошла ошибка. Пожалуйста, повторите попытку позже.',
        // @translators: Сообщение для API, когда невозможно подготовить данные профиля пользователя.
        'profile_unavailable' => 'Не удалось загрузить ваш профиль. Обновите страницу и повторите попытку.',
        // @translators: Сообщение для API, когда оформление заказа заблокировано пустой корзиной.
        'checkout_empty' => 'Ваша корзина пуста. Добавьте товары перед оформлением заказа.',
    ],

    'pages' => [
        'unexpected' => [
            // @translators: Заголовок на общей странице ошибки при непредвиденной ситуации.
            'title' => 'Произошла непредвиденная ошибка',
            // @translators: Описание на общей странице ошибки при непредвиденной ситуации.
            'description' => 'Наша команда уже уведомлена и разбирается с проблемой. Если ошибка повторяется, сообщите службе поддержки идентификатор трассировки.',
            // @translators: Текст основной кнопки на странице ошибки.
            'primary' => 'Вернуться на главную',
            // @translators: Текст дополнительной кнопки на странице ошибки.
            'secondary' => 'Связаться с поддержкой',
        ],
    ],
];
