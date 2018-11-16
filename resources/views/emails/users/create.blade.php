@component('mail::message')
# Hello {{ $name }}, <br>

Your account has been created successfully and we welcome you to <b>{{ config('app.name') }}</b>.

Cheers,<br>
{{ config('app.name') }}
@endcomponent
