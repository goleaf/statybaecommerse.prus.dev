    </div>
    
    <div class="footer mt-large">
        <hr class="hr-muted">
        <div class="footer-muted">
            <p>{{ __('documents.generated_on') }}: {{ now()->format('Y-m-d H:i:s') }}</p>
            <p>© {{ now()->year }} {{ config('app.name') }}. {{ __('documents.all_rights_reserved') }}</p>
            @if(config('app.url'))
                <p>{{ config('app.url') }}</p>
            @endif
        </div>
    </div>
</body>
</html>
