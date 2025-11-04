<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        :root {
            --email-color: {{ $color }};
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/emails/notification.css') }}">
</head>

<body class="email-body">
    <div class="email-header {{ $urgent ? 'urgent' : '' }}">
        <h1>{{ $title }}</h1>
        @if ($urgent)
            <p class="email-urgent-notice">
                ⚠️ URGENT NOTIFICATION
            </p>
        @endif
    </div>

    <div class="email-content">
        <div class="email-notification">
            <span class="email-type-badge">{{ ucfirst($type) }}</span>
            <span class="email-timestamp">
                {{ $created_at->format('M d, Y \a\t H:i') }}
            </span>
        </div>

        <div class="email-message-box">
            {!! nl2br(e($message)) !!}
        </div>

        @if (!empty($tags))
            <div class="email-tags">
                <strong>Tags:</strong>
                @foreach ($tags as $tag)
                    <span class="email-tag">{{ $tag }}</span>
                @endforeach
            </div>
        @endif

        <div class="email-center">
            <a href="{{ \App\Filament\Resources\NotificationResource::getUrl('view', ['record' => $notification]) }}"
               class="email-button">
                View Notification
            </a>
        </div>
    </div>

    <div class="email-footer">
        <p>This notification was sent from {{ config('app.name') }}.</p>
        <p>If you no longer wish to receive these notifications, you can update your preferences in your account
            settings.</p>
    </div>
</body>

</html>
