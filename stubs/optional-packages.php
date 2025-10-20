<?php

if (! class_exists(Awcodes\Curator\Curations\ThumbnailPreset::class)) {
    class_alias(\stdClass::class, Awcodes\Curator\Curations\ThumbnailPreset::class);
}

if (! class_exists(Awcodes\Curator\Glide\DefaultServerFactory::class)) {
    class_alias(\stdClass::class, Awcodes\Curator\Glide\DefaultServerFactory::class);
}

if (! class_exists(Awcodes\Curator\Models\Media::class)) {
    class_alias(\stdClass::class, Awcodes\Curator\Models\Media::class);
}

if (! class_exists(Awcodes\Curator\Resources\MediaResource::class)) {
    class_alias(\stdClass::class, Awcodes\Curator\Resources\MediaResource::class);
}

if (! class_exists(Spatie\MediaLibraryPro\Models\TemporaryUpload::class)) {
    class_alias(\stdClass::class, Spatie\MediaLibraryPro\Models\TemporaryUpload::class);
}

if (! class_exists(Laravel\Telescope\Http\Middleware\Authorize::class)) {
    class_alias(\stdClass::class, Laravel\Telescope\Http\Middleware\Authorize::class);
}

foreach ([
    'BatchWatcher',
    'CacheWatcher',
    'ClientRequestWatcher',
    'CommandWatcher',
    'DumpWatcher',
    'EventWatcher',
    'ExceptionWatcher',
    'GateWatcher',
    'JobWatcher',
    'LogWatcher',
    'MailWatcher',
    'ModelWatcher',
    'NotificationWatcher',
    'QueryWatcher',
    'RedisWatcher',
    'RequestWatcher',
    'ScheduleWatcher',
    'ViewWatcher',
] as $watcher) {
    $class = "Laravel\\Telescope\\Watchers\\{$watcher}";

    if (! class_exists($class)) {
        class_alias(\stdClass::class, $class);
    }
}
