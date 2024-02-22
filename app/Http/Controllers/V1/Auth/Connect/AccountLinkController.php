<?php

namespace App\Http\Controllers\V1\Auth\Connect;

use App\Models\User;
use Stripe\AccountLink;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountLinkController extends Controller
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
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $params = $request->validate([
            'return_url' => ['sometimes','string'],
            'type'       => ['sometimes','string'],
            'collect'    => ['sometimes','string']
        ]);

        /** @var User */
        $user = request()->user();

        if ($user->hasStripeConnectId()) {
            $account = $user->stripeConnectId();
        } else {
            $account = $user->createOrGetStripeConnectAccount([
                'settings' => [
                    'payouts' => [
                        'schedule' => [
                            'interval' => 'manual'
                        ]
                    ],
                ],
            ])->id;
        }

        $defaultParams = [
            'account'     => $account,
            'refresh_url' => route('connect.account_link'),
            'return_url'  => route('homepage'),
            'type'        => 'account_onboarding'
        ];

        $result = AccountLink::create(array_merge($defaultParams, $params), $user->stripeOptions());

        return JsonResource::make($result);
    }
}
