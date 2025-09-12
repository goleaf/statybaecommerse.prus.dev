<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: {{ $color }};
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .urgent {
            background: #dc3545 !important;
        }
        .type-badge {
            display: inline-block;
            background: {{ $color }};
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .tags {
            margin: 15px 0;
        }
        .tag {
            display: inline-block;
            background: #e9ecef;
            color: #495057;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            margin-right: 5px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 12px;
            color: #6c757d;
        }
        .button {
            display: inline-block;
            background: {{ $color }};
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header {{ $urgent ? 'urgent' : '' }}">
        <h1>{{ $title }}</h1>
        @if($urgent)
            <p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">
                ⚠️ URGENT NOTIFICATION
            </p>
        @endif
    </div>
    
    <div class="content">
        <div style="margin-bottom: 20px;">
            <span class="type-badge">{{ ucfirst($type) }}</span>
            <span style="margin-left: 10px; color: #6c757d; font-size: 12px;">
                {{ $created_at->format('M d, Y \a\t H:i') }}
            </span>
        </div>
        
        <div style="background: white; padding: 20px; border-radius: 6px; margin: 20px 0;">
            {!! nl2br(e($message)) !!}
        </div>
        
        @if(!empty($tags))
            <div class="tags">
                <strong>Tags:</strong>
                @foreach($tags as $tag)
                    <span class="tag">{{ $tag }}</span>
                @endforeach
            </div>
        @endif
        
        <div style="text-align: center;">
            <a href="{{ route('filament.admin.resources.notifications.view', $notification) }}" class="button">
                View Notification
            </a>
        </div>
    </div>
    
    <div class="footer">
        <p>This notification was sent from {{ config('app.name') }}.</p>
        <p>If you no longer wish to receive these notifications, you can update your preferences in your account settings.</p>
    </div>
</body>
</html>
