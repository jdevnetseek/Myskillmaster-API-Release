<?php

namespace App\Http\Controllers\V1\Subscription;

use App\Actions\AddFreeTrial;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Services\Subscription\SubscriptionService;
use App\Http\Requests\Subscription\SubscribeRequest;
use Illuminate\Http\Response;
use App\Models\Plan;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->only([
            'subscribe',
            'unsubscribe',
            'changePlan',
            'updatePayment'
        ]);
    }

    public function createSetupIntent(Plan $plan, Request $request)
    {
        $setupIntent = $request->user()->createSetupIntent();

        return response()->json([
            'client_secret' => $setupIntent->client_secret,
            'plan' => $plan
        ]);
    }

    public function subscribe(SubscribeRequest $request)
    {
        $plan = Plan::findOrFail($request->plan);

        resolve(SubscriptionService::class)
            ->setName($request->name)
            ->setSource($request->source)
            ->subscribe($request->user(), $plan);


        $active_subscription = Auth::user()->activeSubscription;

        $data = [
            'transaction_id' => $active_subscription->id,
            'value' => $plan->price / 1000,
            'currency' => "AUD",
            'items' => [[
                'item_id' => $plan->id,
                'item_name' => $plan->name,
                'price' =>   $plan->price / 1000,
                'quantity' => 1,
            ]]
        ];
        return response()->json(
            ['message' => "You have successfully subscribed to {$plan->name}.", "analytics" => $data],
            Response::HTTP_OK
        );
    }

    public function unsubscribe(Request $request)
    {
        resolve(SubscriptionService::class)->unsubscribePlan($request->user());

        return response()->json(
            ['message' => 'Subscription has been cancelled.'],
            Response::HTTP_OK
        );
    }

    /**
     *  Update payment method and resume subscription
     * for current subscription that failed due to card declined
     * @param Request $request
     * @return void
     */
    public function resumePayment(Request $request, Plan $plan)
    {
        $request->validate([
            'source' => ['required', 'string'],
            'plan'   => ['required', 'numeric', 'exists:plans,id'],
        ]);

        $plan = Plan::findOrFail($request->plan);

        resolve(SubscriptionService::class)
            ->setSource($request->input('source'))
            ->resumeSubscription($request->user(), $plan);

        return response()->json(
            ['message' => 'Your subscription has been successfully resumed.'],
            Response::HTTP_OK
        );
    }

    public function freeTrial(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'source' => ['required', 'string'],
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user, $plan, $data) {
            return (new AddFreeTrial)->handle($user, $plan, $data['name'], $data['source']);
        });

        $message = 'Thank you for subscribing to our service!';
        $message .= ' Your subscription is now active and you can start using all';
        $message .= ' of the features included in your plan.';

        return response()->json(['message' => $message], Response::HTTP_OK);
    }
}
