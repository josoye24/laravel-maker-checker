@component('mail::message')
# {{ $body['email_header'] }}


Dear Admin,

{{$body['email_body']}}

Request ID: {{$body['id']}}

Approve By: {{$body['approval_name']}}

Comment: {{$body['approval_comment']}}

Approve Date: {{$body['date']}}



@component('mail::button', ['url' => config('app.url')])
View Request
@endcomponent

Thank you for your attention,

Best regards,

{{ config('app.name') }}
@endcomponent