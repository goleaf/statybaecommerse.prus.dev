<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $report->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 24px;
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .header .subtitle {
            font-size: 14px;
            color: #666;
            margin: 0;
        }
        
        .report-info {
            margin-bottom: 30px;
        }
        
        .report-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .report-info td {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .report-info td:first-child {
            font-weight: bold;
            width: 30%;
        }
        
        .content {
            margin-bottom: 30px;
        }
        
        .content h2 {
            font-size: 18px;
            margin: 20px 0 10px 0;
            color: #333;
        }
        
        .content p {
            margin: 10px 0;
        }
        
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        
        .stat-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $report->name }}</h1>
        <p class="subtitle">{{ __('reports.pdf.generated_on') }}: {{ $generated_at->format('F d, Y H:i') }}</p>
    </div>

    <div class="report-info">
        <table>
            <tr>
                <td>{{ __('reports.pdf.type') }}:</td>
                <td>{{ __("admin.reports.types.{$report->type}") }}</td>
            </tr>
            <tr>
                <td>{{ __('reports.pdf.category') }}:</td>
                <td>{{ __("admin.reports.categories.{$report->category}") }}</td>
            </tr>
            @if($report->date_range)
                <tr>
                    <td>{{ __('reports.pdf.date_range') }}:</td>
                    <td>{{ __("admin.reports.date_ranges.{$report->date_range}") }}</td>
                </tr>
            @endif
            @if($report->start_date && $report->end_date)
                <tr>
                    <td>{{ __('reports.pdf.period') }}:</td>
                    <td>{{ $report->start_date->format('Y-m-d') }} - {{ $report->end_date->format('Y-m-d') }}</td>
                </tr>
            @endif
            <tr>
                <td>{{ __('reports.pdf.generated_by') }}:</td>
                <td>{{ $generated_by }}</td>
            </tr>
            <tr>
                <td>{{ __('reports.pdf.created') }}:</td>
                <td>{{ $report->created_at->format('F d, Y') }}</td>
            </tr>
        </table>
    </div>

    @if($report->description)
        <div class="content">
            <h2>{{ __('reports.pdf.description') }}</h2>
            <p>{{ $report->description }}</p>
        </div>
    @endif

    <div class="stats">
        <div class="stat-item">
            <div class="stat-value">{{ $report->view_count }}</div>
            <div class="stat-label">{{ __('reports.pdf.views') }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $report->download_count }}</div>
            <div class="stat-label">{{ __('reports.pdf.downloads') }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $report->is_active ? __('reports.pdf.active') : __('reports.pdf.inactive') }}</div>
            <div class="stat-label">{{ __('reports.pdf.status') }}</div>
        </div>
    </div>

    @if($report->content)
        <div class="content">
            <h2>{{ __('reports.pdf.content') }}</h2>
            {!! strip_tags($report->content, '<p><br><strong><em><ul><ol><li>') !!}
        </div>
    @endif

    @if($report->filters && count($report->filters) > 0)
        <div class="content">
            <h2>{{ __('reports.pdf.filters') }}</h2>
            <table>
                @foreach($report->filters as $key => $value)
                    <tr>
                        <td>{{ ucfirst(str_replace('_', ' ', $key)) }}:</td>
                        <td>{{ is_array($value) ? implode(', ', $value) : $value }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    <div class="footer">
        <p>{{ __('reports.pdf.footer', ['name' => $report->name, 'date' => $generated_at->format('Y-m-d H:i')]) }}</p>
    </div>
</body>
</html>

