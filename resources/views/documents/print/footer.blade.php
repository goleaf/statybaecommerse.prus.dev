    </div>
    
    <div class="footer mt-large">
        <hr style="border: none; border-top: 1px solid #ddd; margin: 10mm 0;">
        <div style="text-align: center; font-size: 8pt; color: #666;">
            <p>{{ __('documents.generated_on') }}: {{ now()->format('Y-m-d H:i:s') }}</p>
            <p>© {{ now()->year }} {{ config('app.name') }}. {{ __('documents.all_rights_reserved') }}</p>
            @if(config('app.url'))
                <p>{{ config('app.url') }}</p>
            @endif
        </div>
    </div>
</body>
</html>
