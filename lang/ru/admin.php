<?php

declare(strict_types=1);

return [
    // Navigation
    'navigation' => [
        'dashboard' => 'Панель управления',
        'catalog' => 'Каталог',
        'orders' => 'Заказы',
        'customers' => 'Клиенты',
        'marketing' => 'Маркетинг',
        'partners' => 'Партнеры',
        'content' => 'Контент',
        'documents' => 'Документы',
        'settings' => 'Настройки',
        'system' => 'Система',
        'analytics' => 'Аналитика',
        'users' => 'Пользователи',
        'brands' => 'Бренды',
        'zones' => 'Зоны',
        'countries' => 'Страны',
        'locations' => 'Местоположения',
        'categories' => 'Категории',
        'products' => 'Товары',
        'collections' => 'Коллекции',
        'attributes' => 'Атрибуты',
        'reviews' => 'Отзывы',
        'discounts' => 'Скидки',
        'coupons' => 'Купоны',
        'campaigns' => 'Кампании',
        'media' => 'Медиа',
        'cart_items' => 'Товары в корзине',
        'customer_groups' => 'Группы клиентов',
        'legal_pages' => 'Правовые страницы',
        'addresses' => 'Адреса',
        'inventory' => 'Инвентарь',
        'backups' => 'Резервные копии',
        'activity_logs' => 'Журналы активности',
        'currencies' => 'Валюты',
        'partner_tiers' => 'Уровни партнеров',
        'discount_codes' => 'Коды скидок',
        'user_impersonation' => 'Имитация пользователя',
        'system_monitoring' => 'Мониторинг системы',
        'security_audit' => 'Аудит безопасности',
        'data_import_export' => 'Импорт/Экспорт данных',
        'document_templates' => 'Шаблоны документов',
        'enhanced_settings' => 'Расширенные настройки',
        'system_settings' => 'Системные настройки',
    ],
    // Table
    'table' => [
        'name' => 'Имя',
        'email' => 'Эл. почта',
        'orders' => 'Заказы',
        'total_spent' => 'Общая сумма',
        'last_login' => 'Последний вход',
        'status' => 'Статус',
    ],
    // Actions
    'actions' => [
        'view' => 'Просмотр',
        'edit' => 'Редактировать',
        'delete' => 'Удалить',
        'process' => 'Обработать',
        'impersonate' => 'Имитация',
        'start_impersonation' => 'Начать имитацию',
        'stop_impersonation' => 'Остановить имитацию',
        'view_orders' => 'Просмотр заказов',
        'send_notification' => 'Отправить уведомление',
        'activate' => 'Активировать',
        'deactivate' => 'Деактивировать',
    ],
    // Modals
    'modals' => [
        'impersonate_user' => 'Имитация пользователя',
        'impersonate_description' => 'Вы уверены, что хотите начать имитацию этого пользователя? Вы сможете просматривать систему с их точки зрения.',
    ],
    // Notifications
    'notifications' => [
        'cannot_impersonate_admin' => 'Нельзя имитировать администраторов',
        'impersonation_started' => 'Имитация начата',
        'impersonating_user' => 'Сейчас имитируется пользователь: :name',
        'impersonation_stopped' => 'Имитация остановлена',
        'notification_sent' => 'Уведомление отправлено',
    ],
    // Impersonation
    'impersonation' => [
        'active_session' => 'Активная сессия имитации',
        'currently_viewing_as' => 'Сейчас просматриваете как: :name',
        'user_management' => 'Управление пользователями',
        'description' => 'Просматривайте и управляйте пользователями, начинайте сессии имитации в целях безопасности.',
        'guidelines' => 'Руководство по имитации',
        'guideline_1' => 'Используйте имитацию только в целях безопасности и устранения неполадок',
        'guideline_2' => 'Никогда не изменяйте данные пользователя во время имитации',
        'guideline_3' => 'Сессии имитации регистрируются и отслеживаются',
        'guideline_4' => 'Всегда останавливайте имитацию по завершении работы',
    ],
    // Filters
    'filters' => [
        'active_users' => 'Активные пользователи',
        'has_orders' => 'Имеет заказы',
        'recent_activity' => 'Недавняя активность',
    ],
    // Fields
    'fields' => [
        'title' => 'Заголовок',
        'message' => 'Сообщение',
        'notification_type' => 'Тип уведомления',
    ],
    // Notification Types
    'notification_types' => [
        'info' => 'Информация',
        'success' => 'Успех',
        'warning' => 'Предупреждение',
        'danger' => 'Опасность',
    ],
];
