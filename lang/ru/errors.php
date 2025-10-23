<?php

declare(strict_types=1);

use App\Support\ErrorCodes;

return [
    // @translators: Сообщение при отсутствии страницы или записи (HTTP 404).
    ErrorCodes::NOT_FOUND => 'Страница не найдена',

    // @translators: Показывается при непредвиденной ошибке сервера (HTTP 500).
    ErrorCodes::SERVER_ERROR => 'Ошибка сервера',

    // @translators: Используется, когда введённые данные не проходят проверку.
    ErrorCodes::VALIDATION_FAILED => 'Проверьте введённые данные',

    // @translators: Указывает, что пользователь должен войти в систему.
    ErrorCodes::UNAUTHORIZED => 'Нет доступа',

    // @translators: Указывает, что у вошедшего пользователя нет прав для действия.
    ErrorCodes::FORBIDDEN => 'Доступ запрещён',
];
