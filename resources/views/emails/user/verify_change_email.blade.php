@component('mail::message')
# Hi {{ $user->full_name }}

{{ Lang::get('We need to verify your email before we can update it.') }}

{{ Lang::get('Please copy the following code to verify your new email:') }}

@component('mail::panel')
# {{ $token }}
@endcomponent

The {{ config('app.name') }} Team. <br>
@endcomponent
