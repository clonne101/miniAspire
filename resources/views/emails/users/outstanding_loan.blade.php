@component('mail::message')
# Hello {{ $username }},

Please do well to settle your outstanding loan of <b>${{ $remaining_balance }}</b> with a duration 
of <b>{{ $duration }}</b> and a payment frequency of <b>{{ $repayment_frequency }}</b>, before applying 
for another loan but if it is critical then please contact our support team at 
<a href="mailto:support@miniaspire.com">support@miniaspire.com</a>, thank you.

Cheers,<br>
{{ config('app.name') }}
@endcomponent
