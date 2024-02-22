@component('mail::message')
{{'Dear '. $name }},

{{ Lang::get('
We wanted to let you know that your subscription to '.$plan_name.' 
has been confirmed! Your subscription plan details are as follows:
') }}

@component('mail::panel')
<strong>Plan Name:</strong> {{ $plan_name }} <br>

<strong>Plan Price:</strong>  {{ strtoupper($currency) }} {{ $plan_price }} <br>
@endcomponent

@component('mail::button', ['url' => $hosted_invoice_url])
View Invoice Details
@endcomponent

{{ Lang::get('
Your subscription will automatically renew on '.$next_billing_period.'. 
If you have any questions or concerns, please don\'t hesitate to contact us.
')}},


{{ Lang::get('Thank you for your continued support!')}},

{{ Lang::get('Best regards')}},

{{ config('app.name') }}

@endcomponent