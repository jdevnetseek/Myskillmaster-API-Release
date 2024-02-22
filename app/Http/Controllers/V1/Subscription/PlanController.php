<?php

namespace App\Http\Controllers\V1\Subscription;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Plan;
use App\Http\Resources\Subscription\PlanResource;

class PlanController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->only(['show']);
    }
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $plans = Plan::orderByRaw("FIELD(slug, 'pro-plan', 'master-plan', 'basic-plan')")
            ->get();

        return PlanResource::collection($plans);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Plan  $plan
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $user = auth()->user();

        abort_if(!$user->activeSubscription, 404, 'You are not subscribed to any plan');

        if ($user->activeSubscription) {
            $plan = Plan::where('stripe_plan', $user->activeSubscription->stripe_plan)->first();

            return PlanResource::make($plan);
        }
    }
}
