@component('mail::message')
    # {{ __('messages.mail) }}

    {{ __('messages.mail) }}

    @component('mail::button', ['url' => $url])
        {{ __('messages.mail) }}
    @endcomponent

    {{ __('messages.mail) }}

    {{ __('messages.mail) }},<br>
    {{ config('app.name') }}
@endcomponent
