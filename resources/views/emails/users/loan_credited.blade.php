@component('mail::message')
# Hello {{ $username }},

A loan of <b>${{ $credit_amount_total }}</b><i>(fees included)</i> with a duration 
of <b>{{ $duration['date'] }} <i>({{ $duration['humans'] }})</i></b> and a payment 
frequency of <b>{{ $repayment_frequency }}</b> has been credited to your bank account.
<br>
<h2>Bank Details</h2>
Bank: <b>{{ $bank['bank_name'] }}</b><br>
Account: <b>{{ $bank['bank_account'] }}</b>
<br><br>
please contact our support team at 
<a href="mailto:support@miniaspire.com">support@miniaspire.com</a> if you have 
any difficulties, thank you.

Cheers,<br>
{{ config('app.name') }}
@endcomponent
