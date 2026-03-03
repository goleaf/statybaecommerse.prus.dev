<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Document' }}</title>
    <style>
        @page { margin: 20mm; }
    </style>
</head>
<body class="doc-body">
    <div class="doc-header">
        <h1>{{ config('app.name', 'E-Commerce Store') }}</h1>
        <p>{{ config('app.company_address', '') }}</p>
        <p>{{ __('documents.phone') }}: {{ config('app.company_phone', '') }} | {{ __('documents.email') }}: {{ config('app.company_email', config('mail.from.address')) }}</p>
        @if(config('app.company_vat'))
            <p>{{ __('documents.vat_number') }}: {{ config('app.company_vat') }}</p>
        @endif
    </div>
    
    <div class="doc-content">
