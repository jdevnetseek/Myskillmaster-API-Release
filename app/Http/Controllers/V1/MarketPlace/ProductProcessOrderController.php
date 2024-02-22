<?php

namespace App\Http\Controllers\V1\MarketPlace;

use App\Models\User;
use App\Models\Product;
use Stripe\PaymentIntent;
use App\Models\ProductOrder;
use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Payment;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\Product\PaymentReceived;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductProcessOrderController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @todo Add Unit Test
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'product_id'     => 'required',
            'payment_method' => 'required'
        ]);

        /** @var User */
        $user   = $request->user();

        $product = Product::hasMorph('seller', [User::class])
            ->doesntHave('orders')
            ->findOrFail($request->input('product_id'));

        $order = [
            'amount'                    => $product->price_in_cents,
            'currency'                  => $product->currency,
            'customer'                  => $user->stripeId(),
            'receipt_email'             => $user->email,
            'payment_method'            => $request->input('payment_method'),
            'application_fee_amount'    => config('app.application_fee_amount', 0),
            'on_behalf_of'              => $product->seller->stripeConnectId(),
            'transfer_data' => [
                'destination' => $product->seller->stripeConnectId(),
            ],
            'confirm'   => true,
            'metadata' => [
                'product_id'    => $product->id,
                'user_id'       => $user->id
            ]
        ];

        $payment = new Payment(PaymentIntent::create($order, Cashier::stripeOptions()));

        $order = new ProductOrder();
        $order->payment_id = $payment->id;
        $order->amount     = $payment->amount();
        $order->raw_amount = $payment->rawAmount();

        $product->orders()->save($order);

        Mail::to($user)->send(new PaymentReceived($product));

        return $this->respondWithEmptyData();
    }
}
