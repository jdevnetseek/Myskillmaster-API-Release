@component('mail::message')
# Hi {{ \Str::title($recipient->first_name) }},

## The master has cancelled their lesson with you.

<table width="100%" style="max-width:500px;">
    <tr style="background-color: rgb(244, 244, 244); padding: 4px;">
        <td style="text-align: right; padding: 5px;">Lesson Name</td>
        <td style="padding-left: 10px;"><strong>{{ $enrollment->lesson->title }}</strong></td>
    </tr>
    <tr style="background-color: rgb(244, 244, 244); padding:">
        <td style="text-align: right; padding: 4px;">Reference</td>
        <td style="padding-left: 10px; text-transform:uppercase;"><strong>{{ $enrollment->reference_code }}</strong></td>
    </tr>
    <tr>
        <td style="text-align: right; padding: 4px;">Master Name</td>
        <td style="padding-left: 10px;"><strong>{{ data_get($master, 'name') }}</strong></td>
    </tr>
    <tr style="background-color: rgb(244, 244, 244); padding:">
        <td style="text-align: right; padding: 4px;">Schedule Start</td>
        <td style="padding-left: 10px;"><strong>{{ data_get($schedule, 'start') }} ({{ data_get($schedule, 'tz') }})</strong></td>
    </tr>
    <tr>
        <td style="text-align: right; padding: 4px;">Schedule End</td>
        <td style="padding-left: 10px;"><strong>{{ data_get($schedule, 'end') }} ({{ data_get($schedule, 'tz') }})</strong></td>
    </tr>
    <tr style="background-color: rgb(244, 244, 244);">
        <td style="text-align: right; padding: 4px;">Date Cancelled</td>
        <td style="padding-left: 10px;"><strong>{{ $cancelled_at }}</strong></td>
    </tr>
    <tr>
        <td style="text-align: right; padding: 4px;">Reason</td>
        <td style="padding-left: 10px;"><strong>{{ data_get($cancel, 'reason') }}</strong></td>
    </tr>
    <tr style="background-color: rgb(244, 244, 244);">
        <td style="text-align: right; padding: 4px;">Remarks</td>
        <td style="padding-left: 10px; max-width: 100px; overflow-wrap: break-word;">
            <strong>{{ data_get($cancel, 'remarks') }}</strong>
        </td>
    </tr>
</table>

@endcomponent
