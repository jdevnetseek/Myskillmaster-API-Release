@component('mail::message')
{{'Dear '. $name }},

{{ Lang::get('We wanted to let you know that your subscription to '.$old_plan_name.
' has been updated to '.$plan_name.' as of '.now()->format('F j, Y').'. 
    Your new subscription plan details are as follows:') }}

@component('mail::panel')
<strong>Plan Name:</strong> {{ $plan_name }} <br>

<strong>Plan Price:</strong>  {{ strtoupper($currency) }} {{ $plan_price }} <br>
@endcomponent

{{ Lang::get('Your next invoice will reflect the changes made to your subscription plan. If you have any questions or concerns, please don\'t hesitate to contact us.')}},

{{ Lang::get('Thank you for your continued support!')}},

{{ Lang::get('Best regards')}},

{{ config('app.name') }}

@endcomponent