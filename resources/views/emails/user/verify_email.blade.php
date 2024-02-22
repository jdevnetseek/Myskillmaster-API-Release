@component('mail::message')
# Hi {{ $user->full_name }}

{{ Lang::get('Please copy the following code to verify your account:') }}

@component('mail::panel')
# {{ $user->email_verification_code }}
@endcomponent

Your verification code will expire in 3 days.

{{ Lang::get('If you did not request a confirmation, no further action is required.') }}

The {{ config('app.name') }} Team. <br>
@endcomponent
