@component('mail::message')
# Hi {{ $payout->user->first_name }},

<br>

Your payout has been sent on your account ending with <strong><em>{{ data_get($bank, 'last4') }}</em></strong>.

@component('mail::panel')
<strong>Arrival date: </strong> {{ $arrival_date }} <br>

<strong>Payout Amount:</strong> {{ strtoupper($payout->currency) }} {{ $payout->amount }} <br>

<strong>Bank:</strong> {{ data_get($bank, 'name') }} Ending {{ data_get($bank, 'last4') }} <br>

<strong>Date initiated:</strong> {{ $date_requested }}
@endcomponent


## Keep in mind...
It can take up to 2 or more business days for your bank to process and credit your money
to your account ending in <strong><em>{{ data_get($bank, 'last4') }}</em></strong>


@endcomponent
