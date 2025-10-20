<x-mail::message>
# {{ __('mail.contact_message_subject', ['subject' => $contactMessage->subject]) }}

{{ __('frontend/contact.heading.subtitle') }}

<x-mail::panel>
**{{ __('frontend/contact.form.name') }}:** {{ $contactMessage->name }}  
**{{ __('frontend/contact.form.email') }}:** {{ $contactMessage->email }}  
@if($contactMessage->phone)
**{{ __('frontend/contact.form.phone') }}:** {{ $contactMessage->phone }}  
@endif
@if($contactMessage->order_number)
**{{ __('frontend/contact.form.order_number') }}:** {{ $contactMessage->order_number }}  
@endif
</x-mail::panel>

{{ __('frontend/contact.form.message') }}:

> {{ $contactMessage->message }}

{{ __('mail.regards') }},  
{{ config('app.name') }}
</x-mail::message>
