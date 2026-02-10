@component('mail::message')
    # {{ __('messages.mail') }}

    {{ __('messages.mail') }}

    @component('mail::button', ['url' => $url])
        {{ __('messages.mail') }}
    @endcomponent

    @php($expiresIn = (int) ($minutes ?? config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')))
    {{ __('messages.mail', ['count' => $expiresIn]) }}

    {{ __('messages.mail') }}

    {{ __('messages.mail') }},<br>
    {{ config('app.name') }}
@endcomponent
