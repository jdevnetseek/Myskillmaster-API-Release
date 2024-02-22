@component('mail::message')
# Hi {{ \Str::title($recipient->first_name) }},

Your <strong>{{ \Str::title($enrollment->lesson->title) }}</strong> lesson was rescheduled.

-------------

## Reference: <strong>{{ \Str::upper($enrollment->reference_code) }}</strong>

## New schedule</strong>

<strong> Start: <em>{{ data_get($schedule, 'start_date') }}</em> </strong>

<strong> End: <em>{{ data_get($schedule, 'end_date') }}</em> </strong>

------------

@component('mail::panel')
<strong>Reason:</strong> {{ data_get($reschedule, 'reason') }}

<strong>Remarks: </strong> <br>

{{ data_get($reschedule, 'remarks') }}
@endcomponent

<br>

If you are not available to the new schedule, you can login to your account
and click the button below to reschedule it. No action needed if you are okay to the new schedule

@component('mail::button', ['url' => $web_app_enrollment_detail_link])
View Details
@endcomponent

@endcomponent
