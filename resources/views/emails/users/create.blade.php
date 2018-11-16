@component('mail::message')
# Hello {{ $name }}, <br>

Your account has been created successfully and we welcome you to #{{ config('app.name') }}.

Cheers,<br>
{{ config('app.name') }}
@endcomponent
