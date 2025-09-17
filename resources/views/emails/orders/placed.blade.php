@component('mail::message')
    # {{ __('mail.order_confirmation_title') }}

    {{ trans_choice('mail.order_confirmation_body', 1, ['number' => $order->number]) }}

    @component('mail::panel')
        {{ __('mail.total') }}: {{ format_money($order->grand_total_amount, $order->currency_code) }}
    @endcomponent

    @component('mail::button', ['url' => $orderUrl])
        {{ __('mail.view_order_details') }}
    @endcomponent

    {{ __('mail.regards') }},<br>
    {{ config('app.name') }}
@endcomponent
