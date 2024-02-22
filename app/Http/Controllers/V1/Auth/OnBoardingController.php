<?php

namespace App\Http\Controllers\V1\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Gate;

class OnBoardingController extends Controller
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
     * Set the email of the user.
     *
     * @return void
     */
    public function email(Request $request)
    {
        Gate::authorize('update-onboarding-details');

        /** @var User */
        $user = $request->user();

        $payload = $request->validate([
            'email'       => [
                'required',
                "unique:users,email,{$user->id},id"
            ]
        ]);

        $user->email = $payload['email'];
        $user->save();

        return UserResource::make($user->fresh('avatar'));
    }

    /**
     * Handles the request in completing the on boarding process.
     *
     * @param Request $request
     * @return void
     */
    public function complete(Request $request)
    {
        /** @var User */
        $user = $request->user();

        $user->onboard();

        return UserResource::make($user->fresh('avatar'));
    }
}
