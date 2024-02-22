@component('mail::message')
# Hi {{ $payout->user->first_name }},

<br>

A payout on your account ending with <strong><em>{{ data_get($bank, 'last4') }}</em></strong> has failed.

@component('mail::panel')
 <strong>Date requested:</strong> {{ $date_requested }} <br>

 <strong>Payout Amount:</strong> {{ strtoupper($payout->currency) }} {{ $payout->amount }} <br>

 <strong>Bank:</strong> {{ data_get($bank, 'name') }} Ending {{ data_get($bank, 'last4') }} <br>

 <strong>Reason:</strong> <br>
 {{ $payout->failure_message }} <br>
@endcomponent


@endcomponent
