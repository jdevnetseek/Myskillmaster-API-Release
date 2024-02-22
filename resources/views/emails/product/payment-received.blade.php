@component('mail::message')
# Receipt from {{ config('app.name') }}

### Payment Reference:
 {{ $order->payment_id }}

### Date Paid:
{{ $order->created_at->toRfc7231String() }}

### Summary:

@component('mail::table')
| Product                    | Price                   |
|:-------------------------- |:-----------------------:|
| {{ $product->title }}      | ${{ $product->price }}  |
@endcomponent


Thanks,<br>
{{ config('app.name') }}
@endcomponent
