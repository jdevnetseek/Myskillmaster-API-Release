<?php

namespace App\Http\Controllers\V1\Auth\Connect;

use App\Models\User;
use App\Enums\ErrorCodes;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Stripe\Exception\ApiErrorException;
use App\Http\Resources\ConnectAccountResource;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountController extends Controller
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
    public function index()
    {
        /** @var User */
        $user = auth()->user();

        if (! $user->hasStripeConnectId()) {
            return $this->respondWithError(ErrorCodes::STRIPE_CONNECT_NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        return ConnectAccountResource::make($user->getStripeConnectAccount());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /** @var User */
        $user = $request->user();

        try {
            $payload = [
                'individual' => [
                    'email'  => $user->email
                ]
            ];

            $payload = array_replace_recursive($payload, $request->all());

            if (! $user->hasStripeConnectId()) {
                $payload['tos_acceptance'] = [
                    'date'       => now()->unix(),
                    'ip'         => $request->ip(),
                    'user_agent' => $request->userAgent()
                ];

                $account = $user->createStripeConnectAccount($payload);
            } else {
                unset($payload['tos_acceptance']);
                unset($payload['country']);

                $account = $user->updateStripeConnectAccount($payload);
            }

            return ConnectAccountResource::make($account);
        } catch (ApiErrorException $e) {
            abort($e->getHttpStatus(), $e->getMessage());
        }
    }

    /**
     * Handles request for deleting stripe connect account.
     *
     * @return void
     */
    public function destroy()
    {
        /** @var User */
        $user = auth()->user();

        $user->deleteStripeConnectAccount();

        return $this->respondWithEmptyData();
    }
}
