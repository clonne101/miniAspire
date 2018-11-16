@component('mail::message')
# Hello {{ $username }},

Thank you for paying back <b>${{ $debit_amount }}</b> of your 
<b>${{ $loan_amount }}</b> loan with a duration of 
<b>{{ $duration['date'] }} <i>({{ $duration['humans'] }})</i></b> and 
your new remaining balance is <b>${{ $new_remaining_balance }}</b>. This 
was debited from your bank account with details below.
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
