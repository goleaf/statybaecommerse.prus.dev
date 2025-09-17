@component('mail::message')
    # {{ __('mail.verify_email_title') }}

    {{ __('mail.verify_email_intro') }}

    @component('mail::button', ['url' => $url])
        {{ __('mail.verify_email_button') }}
    @endcomponent

    {{ __('mail.verify_email_no_action') }}

    {{ __('mail.regards') }},<br>
    {{ config('app.name') }}
@endcomponent
