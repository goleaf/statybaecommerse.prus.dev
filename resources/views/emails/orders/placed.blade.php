@component('mail::message')
    # {{ __('messages.mail) }}

    {{ trans_choice('messages.mail, 1, ['number' => $order->number]) }}

    @component('mail::panel')
        {{ __('messages.mail) }}: {{ format_money($order->grand_total_amount, $order->currency_code) }}
    @endcomponent

    @component('mail::button', ['url' => $orderUrl])
        {{ __('messages.mail) }}
    @endcomponent

    {{ __('messages.mail) }},<br>
    {{ config('app.name') }}
@endcomponent
