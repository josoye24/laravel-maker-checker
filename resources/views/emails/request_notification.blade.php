@component('mail::message')
# {{ $body['email_header'] }}


Dear Admin,

{{$body['email_body']}}

Request ID: {{$body['id']}}

Request Type: {{$body['request_type']}}

Requested By: {{$body['initiator_name']}}

Comment: {{$body['initiator_comment']}}

Request Date: {{$body['date']}}



@component('mail::button', ['url' => config('app.url')])
View Request
@endcomponent

Thank you for your attention,

Best regards,

{{ config('app.name') }}
@endcomponent