<?php

namespace App\Http\Controllers\V1\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\JsonResource;

class EphemeralKeyController extends Controller
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
        $options = $request->validate([
            'stripe_version' => ['sometimes', 'string']
        ]);

        /**@var User */
        $user = auth()->user();

        return JsonResource::make($user->createEphemeralKey($options));
    }
}
