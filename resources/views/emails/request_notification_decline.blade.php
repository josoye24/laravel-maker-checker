@component('mail::message')
# {{ $body['email_header'] }}


Dear Admin,

{{$body['email_body']}}

Request ID: {{$body['id']}}

Decline By: {{$body['decline_name']}}

Comment: {{$body['decline_comment']}}

Decline Date: {{$body['date']}}



@component('mail::button', ['url' => config('app.url')])
View Request
@endcomponent

Thank you for your attention,

Best regards,

{{ config('app.name') }}
@endcomponent