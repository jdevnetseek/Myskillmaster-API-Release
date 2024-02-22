@component('mail::message')
# Hi {{ $passwordReset->user->full_name }},

{{ Lang::get('You have requested to reset your password for your :app account.', ['app' => config('app.name')]) }}

{{ Lang::get('Simply copy this code') }}

@component('mail::panel')
# {{ $passwordReset->token }}
@endcomponent

{{ Lang::get('and paste it into the app’s reset password form.') }}

{{ Lang::get('Your code will expire in :count minutes.', ['count' => config('auth.passwords.users.expire')]) }}

{{ Lang::get('If you did not make this request, no further action is required.') }}

The {{ config('app.name') }} Team. <br>
@endcomponent
