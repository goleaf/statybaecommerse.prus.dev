<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $report->name }}</title>
    <style>
        @page { margin: 20px; }
    </style>
</head>
<body class="pdf-body">
    <div class="pdf-header">
        <h1>{{ $report->name }}</h1>
        <p class="subtitle">{{ __('reports.pdf.generated_on') }}: {{ $generated_at->format('F d, Y H:i') }}</p>
    </div>

    <div class="pdf-report-info">
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
        <div class="pdf-content">
            <h2>{{ __('reports.pdf.description') }}</h2>
            <p>{{ $report->description }}</p>
        </div>
    @endif

    <div class="pdf-stats">
        <div class="pdf-stat-item">
            <div class="pdf-stat-value">{{ $report->view_count }}</div>
            <div class="pdf-stat-label">{{ __('reports.pdf.views') }}</div>
        </div>
        <div class="pdf-stat-item">
            <div class="pdf-stat-value">{{ $report->download_count }}</div>
            <div class="pdf-stat-label">{{ __('reports.pdf.downloads') }}</div>
        </div>
        <div class="pdf-stat-item">
            <div class="pdf-stat-value">{{ $report->is_active ? __('reports.pdf.active') : __('reports.pdf.inactive') }}</div>
            <div class="pdf-stat-label">{{ __('reports.pdf.status') }}</div>
        </div>
    </div>

    @if($report->content)
        <div class="pdf-content">
            <h2>{{ __('reports.pdf.content') }}</h2>
            {!! strip_tags($report->content, '<p><br><strong><em><ul><ol><li>') !!}
        </div>
    @endif

    @if($report->filters && count($report->filters) > 0)
        <div class="pdf-content">
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

    <div class="pdf-footer">
        <p>{{ __('reports.pdf.footer', ['name' => $report->name, 'date' => $generated_at->format('Y-m-d H:i')]) }}</p>
    </div>
</body>
</html>

