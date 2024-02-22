<?php

namespace App\Http\Controllers\V1\Auth;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;
use Illuminate\Http\Response;
use Laravel\Cashier\PaymentMethod;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Stripe\PaymentMethod as StripePaymentMethod;

class PaymentMethodController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        /** @var User */
        $user = $request->user();

        $collection = $user->paymentMethods();

        /** @var StripePaymentMethod */
        $defaultPaymentMethod = $user->defaultPaymentMethod();

        /**
         * We are going to map the data to easily identify which is the a default payment method.
         */
        $paymentMethods = $collection->map(function (PaymentMethod $paymentMethod) use ($defaultPaymentMethod) {
            $stripePaymentMethod = $paymentMethod->asStripePaymentMethod();

            data_set(
                $stripePaymentMethod,
                'is_default',
                optional($defaultPaymentMethod)->id === $stripePaymentMethod->id
            );

            return $stripePaymentMethod;
        });

        return JsonResource::collection($paymentMethods);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'source' => ['required', 'string']
        ]);

        /** @var User */
        $user = $request->user();

        if (!$user->stripe_id) {
            $user->createAsStripeCustomer();
        }

        $paymentMethod = StripePaymentMethod::create([
            'type' => 'card',
            'card' => [
                'token' => $request->input('source')
            ]
        ], $user->stripeOptions());

        $user->addPaymentMethod($paymentMethod);

        return JsonResource::make($paymentMethod);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        /** @var User */
        $user = $request->user();

        /** @var PaymentMethod */
        $paymentMethod = $user->findPaymentMethod($id);

        return JsonResource::make(optional($paymentMethod)->asStripePaymentMethod());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        throw new Exception('Not Implemented');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        /** @var User */
        $user = $request->user();

        /** @var PaymentMethod */
        $paymentMethod = $user->findPaymentMethod($id);

        $paymentMethod->delete();

        return response()->json([], Response::HTTP_OK);
    }

    /**
     * Handle request of marking a card as default.
     *
     * @param Request $request
     * @param [type] $id
     * @return void
     */
    public function markAsDefault(Request $request, $id)
    {
        /** @var User */
        $user = $request->user();

        $user->updateDefaultPaymentMethod($id);

        return response()->json([], Response::HTTP_OK);
    }
}
