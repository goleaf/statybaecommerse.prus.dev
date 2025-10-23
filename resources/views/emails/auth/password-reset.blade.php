@component('mail::message')
    # {{ __('mail.reset_password_title') }}

    {{ __('mail.reset_password_intro') }}

    @component('mail::button', ['url' => $url])
        {{ __('mail.reset_password_button') }}
    @endcomponent

    @php($expiresIn = (int) ($minutes ?? config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')))
    {{ trans_choice('mail.reset_password_expire', $expiresIn, ['count' => $expiresIn]) }}

    {{ __('mail.reset_password_no_action') }}

    {{ __('mail.regards') }},<br>
    {{ config('app.name') }}
@endcomponent
