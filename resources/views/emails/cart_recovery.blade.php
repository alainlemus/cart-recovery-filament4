@component('mail::message')
# Hello {{ $data['email'] }}

{!! nl2br(e($data['description'])) !!}

@component('mail::button', ['url' => $data['checkout_url'] ?? '#' ])
Recover Your Cart
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
