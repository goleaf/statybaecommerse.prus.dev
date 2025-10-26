<?php

declare(strict_types=1);

return [
    'titles' => [
        // @translators: Сообщение при отсутствии страницы или записи (HTTP 404).
        ErrorCodes::key(ErrorCodes::NOT_FOUND) => 'Страница не найдена',

        // @translators: Показывается при непредвиденной ошибке сервера (HTTP 500).
        ErrorCodes::key(ErrorCodes::SERVER_ERROR) => 'Ошибка сервера',

        // @translators: Используется, когда введённые данные не проходят проверку.
        ErrorCodes::key(ErrorCodes::VALIDATION_FAILED) => 'Проверьте введённые данные',

        // @translators: Указывает, что пользователь должен войти в систему.
        ErrorCodes::key(ErrorCodes::UNAUTHORIZED) => 'Нет доступа',

        // @translators: Указывает, что у вошедшего пользователя нет прав для действия.
        ErrorCodes::key(ErrorCodes::FORBIDDEN) => 'Доступ запрещён',

        // @translators: Показывается, когда заказ с указанным номером не найден.
        ErrorCodes::key(ErrorCodes::ORDER_NOT_FOUND) => 'Заказ :order не найден.',

        // @translators: Сообщает о недостаточном количестве товара для указанного SKU.
        ErrorCodes::key(ErrorCodes::INVENTORY_INSUFFICIENT) => 'Недостаточно запасов для артикула :sku.',
        // @translators: Показывается, когда не удалось загрузить профиль авторизованного пользователя.
        ErrorCodes::key(ErrorCodes::PROFILE_UNAVAILABLE) => 'Профиль недоступен',
        // @translators: Показывается, когда оформление заказа прекращается из-за пустой корзины.
        ErrorCodes::key(ErrorCodes::CHECKOUT_CART_EMPTY) => 'Корзина пуста',
    ],

    'messages' => [
        // @translators: Универсальное сообщение для API при неожиданных ошибках сервера.
        ErrorCodes::key(ErrorCodes::SERVER_ERROR) => 'Произошла ошибка. Пожалуйста, повторите попытку позже.',
        // @translators: Сообщение для API, когда введённые данные не проходят проверку.
        ErrorCodes::key(ErrorCodes::VALIDATION_FAILED) => 'Проверьте введённые данные и попробуйте снова.',
        // @translators: Сообщение для API, когда требуется авторизация.
        ErrorCodes::key(ErrorCodes::UNAUTHORIZED) => 'Пожалуйста, войдите в систему, чтобы продолжить.',
        // @translators: Сообщение для API, когда у пользователя недостаточно прав.
        ErrorCodes::key(ErrorCodes::FORBIDDEN) => 'У вас недостаточно прав для выполнения этого действия.',
        // @translators: Сообщение для API, когда ресурс не найден.
        ErrorCodes::key(ErrorCodes::NOT_FOUND) => 'Запрашиваемый ресурс не найден.',
        // @translators: Сообщение для API, когда невозможно подготовить данные профиля пользователя.
        ErrorCodes::key(ErrorCodes::PROFILE_UNAVAILABLE) => 'Не удалось загрузить ваш профиль. Обновите страницу и повторите попытку.',
        // @translators: Сообщение для API, когда оформление заказа заблокировано пустой корзиной.
        ErrorCodes::key(ErrorCodes::CHECKOUT_CART_EMPTY) => 'Ваша корзина пуста. Добавьте товары перед оформлением заказа.',
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
