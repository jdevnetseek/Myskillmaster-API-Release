@component('mail::message')
{{'Dear '. $name }},

{{ Lang::get('We\'re sorry to see you go! We wanted to confirm that your subscription to '.$plan_name.' has been cancelled.
 Your account will remain active until the end of your current billing period, which ends on '.$plan_ends_at.'. 
 After that, you will no longer be charged for this subscription.') }}

{{ Lang::get('If you change your mind and want to resubscribe, you can do so at any time by visiting our website and selecting the subscription plan that\'s right for you.')}},

{{ Lang::get('Thank you for being a valued customer, and we hope to see you again soon!')}},

{{ Lang::get('Best regards')}},

{{ config('app.name') }}

@endcomponent