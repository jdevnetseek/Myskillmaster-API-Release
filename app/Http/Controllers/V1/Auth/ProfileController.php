<?php

namespace App\Http\Controllers\V1\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UpdateProfileRequest;

class ProfileController extends Controller
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
     * Show current authenticated user profile
     *
     * @return UserResource
     */
    public function index(): UserResource
    {
        return new UserResource(auth()->user()->load('avatar', 'address.state'));
    }

    /**
     * Update current authenticated user profile
     *
     * @param UpdateUserRequest $request
     * @return UserResource
     */
    public function update(UpdateProfileRequest $request): UserResource
    {
        $user = DB::transaction(function () use ($request) {
            /** @var User */
            $user = auth()->user();

            $user->update($request->validated());

            if ($request->has('avatar')) {
                /**
                 * If the avatar parameter value is null,
                 * we will assume that the user was trying to remove the avatar.
                 *
                 * Else it was trying to set a new avatar.
                 */
                $avatarId = $request->input('avatar');
                if (is_null($avatarId)) {
                    $user->removeAvatar();
                } else {
                    $user->setAvatarByMediaId($avatarId);
                }
            }

            return $user;
        });

        return new UserResource($user->load('avatar', 'address.state'));
    }
}
