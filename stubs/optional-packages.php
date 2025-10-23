<?php

declare(strict_types=1);

namespace Awcodes\Curator\Curations {
    if (! class_exists(ThumbnailPreset::class)) {
        class ThumbnailPreset {}
    }
}

namespace Awcodes\Curator\Glide {
    if (! class_exists(DefaultServerFactory::class)) {
        class DefaultServerFactory {}
    }
}

namespace Awcodes\Curator\Models {
    if (! class_exists(Media::class)) {
        class Media {}
    }
}

namespace Awcodes\Curator\Resources {
    if (! class_exists(MediaResource::class)) {
        class MediaResource {}
    }
}

namespace Spatie\MediaLibraryPro\Models {
    if (! class_exists(TemporaryUpload::class)) {
        class TemporaryUpload {}
    }
}

namespace Laravel\Telescope\Http\Middleware {
    if (! class_exists(Authorize::class)) {
        class Authorize
        {
            public function handle(mixed $request, \Closure $next): mixed
            {
                return $next($request);
            }
        }
    }
}

namespace Laravel\Telescope\Watchers {
    if (! class_exists(BatchWatcher::class)) {
        class BatchWatcher {}
    }

    if (! class_exists(CacheWatcher::class)) {
        class CacheWatcher {}
    }

    if (! class_exists(ClientRequestWatcher::class)) {
        class ClientRequestWatcher {}
    }

    if (! class_exists(CommandWatcher::class)) {
        class CommandWatcher {}
    }

    if (! class_exists(DumpWatcher::class)) {
        class DumpWatcher {}
    }

    if (! class_exists(EventWatcher::class)) {
        class EventWatcher {}
    }

    if (! class_exists(ExceptionWatcher::class)) {
        class ExceptionWatcher {}
    }

    if (! class_exists(GateWatcher::class)) {
        class GateWatcher {}
    }

    if (! class_exists(JobWatcher::class)) {
        class JobWatcher {}
    }

    if (! class_exists(LogWatcher::class)) {
        class LogWatcher {}
    }

    if (! class_exists(MailWatcher::class)) {
        class MailWatcher {}
    }

    if (! class_exists(ModelWatcher::class)) {
        class ModelWatcher {}
    }

    if (! class_exists(NotificationWatcher::class)) {
        class NotificationWatcher {}
    }

    if (! class_exists(QueryWatcher::class)) {
        class QueryWatcher {}
    }

    if (! class_exists(RedisWatcher::class)) {
        class RedisWatcher {}
    }

    if (! class_exists(RequestWatcher::class)) {
        class RequestWatcher {}
    }

    if (! class_exists(ScheduleWatcher::class)) {
        class ScheduleWatcher {}
    }

    if (! class_exists(ViewWatcher::class)) {
        class ViewWatcher {}
    }
}
