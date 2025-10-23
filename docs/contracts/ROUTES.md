# API Route Map

> Generated from the `/routes/api.php` definitions for the `/api/v1` namespace.

| Method | URI | Name | Action | Middleware |
| --- | --- | --- | --- | --- |
| GET | api/v1/user | api.v1.user.show | App\\Http\\Controllers\\Api\\AuthenticatedUserController | auth:sanctum, abilities:profile.read, throttle:api.profile, throttle:api.read |
| POST | api/v1/autocomplete-search | api.v1.autocomplete.search | App\\Http\\Controllers\\Api\\AutocompleteSearchController | auth:sanctum, abilities:system.autocomplete, throttle:api.autocomplete |
| GET | api/v1/notifications | api.v1.notifications.index | App\\Http\\Controllers\\Api\\NotificationController@index | auth:sanctum, throttle:api.notifications.read, abilities:notifications.read |
| GET | api/v1/notifications/stats | api.v1.notifications.stats | App\\Http\\Controllers\\Api\\NotificationController@stats | auth:sanctum, throttle:api.notifications.read, abilities:notifications.read |
| GET | api/v1/notifications/search | api.v1.notifications.search | App\\Http\\Controllers\\Api\\NotificationController@search | auth:sanctum, throttle:api.notifications.read, abilities:notifications.read |
| GET | api/v1/notifications/{notification} | api.v1.notifications.show | App\\Http\\Controllers\\Api\\NotificationController@show | auth:sanctum, throttle:api.notifications.read, abilities:notifications.read |
| POST | api/v1/notifications/mark-all-read | api.v1.notifications.mark-all-read | App\\Http\\Controllers\\Api\\NotificationController@markAllAsRead | auth:sanctum, throttle:api.notifications.write, abilities:notifications.manage |
| POST | api/v1/notifications/mark-all-unread | api.v1.notifications.mark-all-unread | App\\Http\\Controllers\\Api\\NotificationController@markAllAsUnread | auth:sanctum, throttle:api.notifications.write, abilities:notifications.manage |
| POST | api/v1/notifications/{notification}/mark-read | api.v1.notifications.mark-as-read | App\\Http\\Controllers\\Api\\NotificationController@markAsRead | auth:sanctum, throttle:api.notifications.write, abilities:notifications.manage |
| POST | api/v1/notifications/{notification}/mark-unread | api.v1.notifications.mark-as-unread | App\\Http\\Controllers\\Api\\NotificationController@markAsUnread | auth:sanctum, throttle:api.notifications.write, abilities:notifications.manage |
| DELETE | api/v1/notifications/{notification} | api.v1.notifications.destroy | App\\Http\\Controllers\\Api\\NotificationController@destroy | auth:sanctum, throttle:api.notifications.write, abilities:notifications.manage |

Each route requires a valid Sanctum token with the listed ability (or a first-party session with equivalent privileges).
