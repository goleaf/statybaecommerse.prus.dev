<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Document' }}</title>
    <style>
        @page {
            margin: 20mm;
            @top-center {
                content: "{{ config('app.name') }} - {{ $title ?? 'Document' }}";
                font-family: Arial, sans-serif;
                font-size: 10pt;
                color: #666;
            }
            @bottom-center {
                content: "Page " counter(page) " of " counter(pages);
                font-family: Arial, sans-serif;
                font-size: 8pt;
                color: #666;
            }
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10mm;
            margin-bottom: 10mm;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24pt;
            color: #333;
        }
        
        .header p {
            margin: 2mm 0;
            font-size: 10pt;
            color: #666;
        }
        
        .document-content {
            min-height: calc(100vh - 40mm);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 5mm 0;
        }
        
        th, td {
            padding: 3mm;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 9pt;
        }
        
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .total-row {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .mt-large {
            margin-top: 10mm;
        }
        
        .mb-large {
            margin-bottom: 10mm;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name', 'E-Commerce Store') }}</h1>
        <p>{{ config('app.company_address', '') }}</p>
        <p>{{ __('documents.phone') }}: {{ config('app.company_phone', '') }} | {{ __('documents.email') }}: {{ config('app.company_email', config('mail.from.address')) }}</p>
        @if(config('app.company_vat'))
            <p>{{ __('documents.vat_number') }}: {{ config('app.company_vat') }}</p>
        @endif
    </div>
    
    <div class="document-content">
