<?php

namespace App\Http\Controllers\V1\Admin\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;

class AccountSettingsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Handles the incomming request for blocking user.
     *
     * @todo Add unit test
     *
     * @param User $user
     * @return void
     */
    public function blocked(User $user)
    {
        if (blank($user->blocked_at)) {
            // Mark user as blocked.
            $user->blocked_at = now();
            $user->save();

            // Log out user on all devices.
            $user->tokens()->delete();

            // @todo Send Email to user that he was blocked
        }

        return UserResource::make($user->load('avatar'));
    }

    /**
     * Handles the incomming request for unblocking a user.
     *
     * @todo Add unit test
     *
     * @param User $user
     * @return void
     */
    public function unblocked(User $user)
    {
        if (filled($user->blocked_at)) {
            $user->blocked_at = null;
            $user->save();

            // @todo Send email that the account was restored.
        }

        return UserResource::make($user->load('avatar'));
    }
}
